<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Package;
use App\Models\PackageDeparture;
use App\Models\Payment;
use App\Models\User;
use App\Services\Booking\ManualRefundService;
use App\Services\Payment\RazorpayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Online booking for group packages — no supplier API: paying confirms the
 * seats. Payment reuses the shared Razorpay routes; FrontendController
 * hands package payments here (Payment.purpose 'booking' for the first
 * payment, 'package_balance' for the balance).
 *
 * Rules (client's decision, 2026-10-14):
 * - Pay a booking amount per person now and the balance later, or in full.
 * - The balance is due BALANCE_DUE_DAYS_BEFORE days before departure; still
 *   unpaid on the departure day, the booking is cancelled and the booking
 *   amount forfeited (no refund) — see forfeitUnpaid().
 * - Guests can ask to cancel up to CANCEL_DAYS_BEFORE days before departure;
 *   the team decides the refund and pays it from the admin.
 */
class PackageBookingService
{
    public const SEAT_HOLD_MINUTES = 30;

    public const BALANCE_DUE_DAYS_BEFORE = 2;

    public const CANCEL_DAYS_BEFORE = 7;

    public const MAX_TRAVELLERS = 20;

    public const PURPOSE_BALANCE = 'package_balance';

    /**
     * Infants up to and including this age travel free (client, 2026-10-16):
     * no price, no booking amount, and no seat of their own. Everyone older
     * pays the per-person price.
     */
    public const FREE_INFANT_MAX_AGE = 3;

    public static function isFreeInfant(int $age): bool
    {
        return $age <= self::FREE_INFANT_MAX_AGE;
    }

    public function __construct(protected RazorpayService $razorpay) {}

    /**
     * Creates the booking awaiting payment. Seats are checked under a lock on
     * the departure, so two guests can't both take the last seat.
     *
     * @param  array<int, array{full_name: string, gender: string, age: int}>  $travellers
     * @param  array{email: string, phone: string, phone_code: string, special_requests?: ?string}  $contact
     *
     * @throws RuntimeException with a message for the guest
     */
    public function create(User $user, Package $package, int $departureId, string $optionLabel, array $travellers, array $contact, string $plan): Booking
    {
        if (! $package->acceptsOnlineBooking()) {
            throw new RuntimeException('Online booking is not open for this package. Please contact us to book.');
        }

        $option = collect($package->priceOptions())->firstWhere('label', $optionLabel);
        if (! $option) {
            throw new RuntimeException('Please choose a room sharing option.');
        }

        return DB::transaction(function () use ($user, $package, $departureId, $option, $travellers, $contact, $plan) {
            $departure = PackageDeparture::whereKey($departureId)->where('package_id', $package->id)->lockForUpdate()->first();
            if (! $departure || ! $departure->isOpenForBooking()) {
                throw new RuntimeException('Sorry, this departure date is no longer open for booking. Please choose another date.');
            }

            // The same form sent twice (double click, slow connection, back
            // and resubmit): hand back the unpaid booking it already made
            // instead of a second one holding more seats. The departure lock
            // above makes simultaneous clicks wait for each other.
            $again = $this->unpaidDuplicate($user, $package, $departure, $option['label'], $plan, $travellers);
            if ($again) {
                return $again;
            }

            // Only paying travellers take a seat and are charged.
            $infants = count(array_filter($travellers, fn ($t) => self::isFreeInfant((int) $t['age'])));
            $count = count($travellers) - $infants;
            if ($count < 1) {
                throw new RuntimeException('Please add at least one traveller older than '.self::FREE_INFANT_MAX_AGE.' years — infants travel free with an adult.');
            }
            $left = $departure->seatsLeft();
            if ($left !== null && $count > $left) {
                throw new RuntimeException($left === 0
                    ? 'Sorry, this departure is sold out. Please choose another date.'
                    : "Only {$left} seat".($left === 1 ? ' is' : 's are').' left on this departure. Please reduce the number of travellers or choose another date.');
            }

            $quote = PackagePricingService::quote($package, $departure, $option['price'], $count, $plan);

            $booking = Booking::create([
                'reference' => 'TYT'.strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'vertical' => 'package',
                'package_id' => $package->id,
                'package_departure_id' => $departure->id,
                'package_option' => $option['label'],
                'package_price_per_person' => $quote['per_person'],
                'package_payment_plan' => $quote['plan'],
                'package_balance_amount' => $quote['balance']['base'],
                'package_balance_due_on' => $departure->start_date->copy()->subDays(self::BALANCE_DUE_DAYS_BEFORE),
                'check_in' => $departure->start_date->toDateString(),
                'check_out' => $departure->end_date?->toDateString(),
                'pax_adults' => $count, // paying travellers (seats)
                'pax_children' => 0,
                'pax_infants' => $infants, // free, no seat
                // The lead traveller is the first one who isn't an infant.
                'lead_guest_name' => collect($travellers)->first(fn ($t) => ! self::isFreeInfant((int) $t['age']))['full_name'],
                'guest_email' => $contact['email'],
                'guest_phone' => $contact['phone'],
                'guest_phone_code' => $contact['phone_code'],
                'special_requests' => $contact['special_requests'] ?? null,
                'base_amount' => $quote['subtotal'],
                'tax_amount' => $quote['gst'],
                'total_amount' => $quote['now']['total'],
                'razorpay_recovery' => $quote['now']['gateway'],
                'gst_slab' => PackagePricingService::GST_RATE,
                'currency' => 'INR',
                'status' => 'pending_payment',
            ]);

            foreach ($travellers as $t) {
                $booking->travelers()->create([
                    'full_name' => $t['full_name'],
                    'gender' => $t['gender'],
                    'age' => $t['age'],
                    'traveler_type' => self::isFreeInfant((int) $t['age']) ? 'infant' : 'adult',
                ]);
            }

            return $booking;
        });
    }

    /** This guest's unpaid booking from the last 10 minutes with exactly these choices and travellers. */
    protected function unpaidDuplicate(User $user, Package $package, PackageDeparture $departure, string $option, string $plan, array $travellers): ?Booking
    {
        $key = fn ($list) => collect($list)->map(fn ($t) => mb_strtolower(trim($t['full_name'])).'|'.(int) $t['age'].'|'.$t['gender'])->sort()->values()->all();
        $wanted = $key($travellers);

        return Booking::with('travelers')
            ->where('user_id', $user->id)->where('vertical', 'package')
            ->where('package_id', $package->id)->where('package_departure_id', $departure->id)
            ->where('package_option', $option)
            ->whereIn('status', ['pending_payment', 'payment_failed'])
            ->where('created_at', '>=', now()->subMinutes(10))
            ->latest('id')->get()
            // "part" is stored as "full" when the package has no booking amount.
            ->first(fn (Booking $b) => ($b->package_payment_plan === $plan || ($plan === 'part' && (float) $package->booking_amount <= 0))
                && $key($b->travelers->map(fn ($t) => ['full_name' => $t->full_name, 'age' => $t->age, 'gender' => $t->gender])) === $wanted);
    }

    /** Amount of the payment for $purpose: the first payment, or the balance. */
    public function amountFor(Booking $booking, string $purpose): float
    {
        return $purpose === self::PURPOSE_BALANCE
            ? PackagePricingService::charge((float) $booking->package_balance_amount)['total']
            : (float) $booking->total_amount;
    }

    /** Reuses an unpaid Razorpay order for the same amount (guest retrying), else makes one. */
    public function paymentFor(Booking $booking, string $purpose): Payment
    {
        $amount = $this->amountFor($booking, $purpose);

        $payment = $booking->payments()->where('status', 'created')->where('purpose', $purpose)->latest()->first();
        if ($payment && abs((float) $payment->amount - $amount) < 0.01) {
            return $payment;
        }

        $order = $this->razorpay->createOrder($amount, $booking->reference);

        return Payment::create([
            'booking_id' => $booking->id,
            'razorpay_order_id' => $order['id'],
            'amount' => $amount,
            'currency' => $booking->currency ?: 'INR',
            'status' => 'created',
            'purpose' => $purpose,
        ]);
    }

    /**
     * Payment captured (Checkout callback or webhook — either may come first,
     * or both): confirms the booking, or clears the balance. Locked and
     * status-guarded, so running twice changes nothing.
     */
    public function confirmAfterPayment(Payment $payment): void
    {
        $refund = DB::transaction(function () use ($payment) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->first();
            $booking = Booking::whereKey($payment->booking_id)->lockForUpdate()->first();

            if ($payment->status !== 'created' && $payment->status !== 'failed') {
                return null; // already handled
            }
            $payment->update(['status' => 'captured']);

            if ($payment->purpose === self::PURPOSE_BALANCE) {
                if ($booking->status !== 'confirmed' || (float) $booking->package_balance_amount <= 0) {
                    return [$booking, $payment, 'The balance was paid after this booking was already '.($booking->status === 'cancelled' ? 'cancelled' : 'settled').'.'];
                }

                $charge = PackagePricingService::charge((float) $booking->package_balance_amount);
                $booking->update([
                    'package_balance_amount' => 0,
                    'total_amount' => round((float) $booking->total_amount + (float) $payment->amount, 2),
                    'razorpay_recovery' => round((float) $booking->razorpay_recovery + $charge['gateway'], 2),
                    'admin_note' => $booking->adminNoteWith(now()->format('j M Y, g:i A').' — guest paid the balance of INR '.number_format((float) $payment->amount, 2).' online.'),
                ]);

                return null;
            }

            if (! in_array($booking->status, ['pending_payment', 'payment_failed'], true)) {
                return null;
            }

            // Seats are held for SEAT_HOLD_MINUTES; a payment finished after
            // that may find the departure filled up in the meantime.
            $departure = $booking->packageDeparture()->lockForUpdate()->first();
            $left = $departure?->seatsLeft($booking->id);
            if ($departure === null || ($left !== null && $left < (int) $booking->pax_adults)) {
                return [$booking, $payment, 'The seats were no longer available when the payment completed.'];
            }

            $booking->update(['status' => 'confirmed']);

            return null;
        });

        if ($refund) {
            [$booking, $payment, $why] = $refund;
            $this->refundPayment($booking, $payment, $why);
        }
    }

    /** Refunds one whole payment that can't be used (sold out, or paid too late). */
    protected function refundPayment(Booking $booking, Payment $payment, string $why): void
    {
        $firstPayment = $payment->purpose !== self::PURPOSE_BALANCE;

        try {
            $this->razorpay->refund((string) $payment->razorpay_payment_id);
            $payment->update(['status' => 'refunded', 'refund_amount' => $payment->amount, 'refund_reason' => $why]);
            $booking->update(array_filter([
                'status' => $firstPayment ? 'refunded' : null,
                'cancellation_reason' => $firstPayment ? $why.' Refunded in full.' : null,
                'admin_note' => $booking->adminNoteWith(now()->format('j M Y, g:i A').' — '.$why.' INR '.number_format((float) $payment->amount, 2).' refunded automatically.'),
            ]));
        } catch (\Throwable $e) {
            Log::channel('tripjack')->error('package_refund_failed', ['booking_id' => $booking->id, 'payment_id' => $payment->id, 'message' => $e->getMessage()]);
            $booking->update(array_filter([
                'status' => $firstPayment ? 'failed_needs_review' : null,
                'manual_refund_due_at' => now(),
                'admin_note' => $booking->adminNoteWith(now()->format('j M Y, g:i A').' — '.$why.' The automatic refund of INR '.number_format((float) $payment->amount, 2).' failed: refund it in the Razorpay dashboard, then click "Record manual refund".'),
            ]));
        }
    }

    /** The guest's own "Cancel booking" is open until CANCEL_DAYS_BEFORE days before departure. */
    public static function guestCanCancel(Booking $booking): bool
    {
        return $booking->vertical === 'package'
            && $booking->status === 'confirmed'
            && $booking->cancellation_requested_at === null
            && $booking->check_in !== null
            && today()->lte(\Illuminate\Support\Carbon::parse($booking->check_in)->subDays(self::CANCEL_DAYS_BEFORE));
    }

    /** Last day the guest can ask to cancel online. */
    public static function cancelDeadline(Booking $booking): ?\Illuminate\Support\Carbon
    {
        return $booking->check_in ? \Illuminate\Support\Carbon::parse($booking->check_in)->subDays(self::CANCEL_DAYS_BEFORE) : null;
    }

    /**
     * Guest asks to cancel. Nothing is refunded here: the team decides the
     * refund (client's decision) and pays it with "Cancel & refund".
     */
    public function requestCancellation(Booking $booking, ?string $reason): bool
    {
        return DB::transaction(function () use ($booking, $reason) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->first();
            if (! self::guestCanCancel($booking)) {
                return false;
            }

            $booking->update([
                'cancellation_requested_at' => now(),
                'admin_note' => $booking->adminNoteWith(now()->format('j M Y, g:i A').' — guest asked to cancel'.($reason ? ': "'.trim($reason).'"' : '').'. Decide the refund and click "Cancel & refund".'),
            ]);

            return true;
        });
    }

    /**
     * Cancellation charges from the Cancellation Policy (section 2), by days
     * before departure: [at least this many days, % charged]. Checked top
     * to bottom; keep in step with pages/cancellation.blade.php.
     */
    public const CANCELLATION_CHARGES = [
        [31, 10],
        [21, 25],
        [15, 50],
        [8, 75],
        [0, 100],
    ];

    /** Payments that can be refunded through Razorpay (not ones taken offline). */
    protected static function onlineRefundablePayments(Booking $booking)
    {
        return ManualRefundService::refundablePayments($booking)->reject(fn (Payment $p) => $p->isOffline())->values();
    }

    /** What can still be refunded through Razorpay across this booking's payments. */
    public static function refundableAmount(Booking $booking): float
    {
        return round((float) self::onlineRefundablePayments($booking)->sum(fn (Payment $p) => ManualRefundService::remaining($p)), 2);
    }

    /** Paid to the team offline and not yet returned — must be refunded outside the website. */
    public static function offlinePaidAmount(Booking $booking): float
    {
        return round((float) ManualRefundService::refundablePayments($booking)->filter(fn (Payment $p) => $p->isOffline())->sum(fn (Payment $p) => ManualRefundService::remaining($p)), 2);
    }

    /**
     * The refund the cancellation policy gives, counted from the day the guest
     * asked to cancel (or today) to departure. Staff can change it, e.g. when
     * a supplier's charges are higher.
     *
     * @return array{days: int, charge_percent: int, refund: float}
     */
    public static function suggestedRefund(Booking $booking): array
    {
        $from = ($booking->cancellation_requested_at ?? now())->copy()->startOfDay();
        $days = $booking->check_in ? max(0, (int) $from->diffInDays(\Illuminate\Support\Carbon::parse($booking->check_in)->startOfDay(), false)) : 0;
        $percent = 100;
        foreach (self::CANCELLATION_CHARGES as [$minDays, $charge]) {
            if ($days >= $minDays) {
                $percent = $charge;
                break;
            }
        }

        $online = self::refundableAmount($booking);
        $paid = $online + self::offlinePaidAmount($booking);
        $refund = round($paid * (100 - $percent) / 100, 2);

        return ['days' => $days, 'charge_percent' => $percent, 'refund' => min($online, $refund)];
    }

    /**
     * Staff cancel the booking and refund $amount (0 = no refund) through
     * Razorpay, newest payment first. A failed refund leaves the booking
     * flagged "refund needs action" for a manual refund.
     *
     * @return array{success: bool, message: string}
     */
    public function cancelAndRefund(Booking $booking, float $amount, string $staff): array
    {
        $amount = round(max(0, $amount), 2);
        $max = self::refundableAmount($booking);
        if ($amount > $max + 0.001) {
            return ['success' => false, 'message' => sprintf('The refund cannot be more than INR %s (what the guest has paid and not yet been refunded).', number_format($max, 2))];
        }
        if (! in_array($booking->status, ['confirmed', 'pending_payment', 'payment_failed'], true)) {
            return ['success' => false, 'message' => 'This booking is already '.str_replace('_', ' ', $booking->status).'.'];
        }

        $refunded = 0.0;
        $failed = null;
        $remaining = $amount;
        foreach (self::onlineRefundablePayments($booking)->sortByDesc('id') as $payment) {
            if ($remaining <= 0) {
                break;
            }
            $part = round(min($remaining, ManualRefundService::remaining($payment)), 2);
            try {
                $this->razorpay->refund((string) $payment->razorpay_payment_id, $part);
            } catch (\Throwable $e) {
                Log::channel('tripjack')->error('package_cancel_refund_failed', ['booking_id' => $booking->id, 'payment_id' => $payment->id, 'message' => $e->getMessage()]);
                $failed = $e->getMessage();
                break;
            }
            $total = round((float) $payment->refund_amount + $part, 2);
            $payment->update([
                'refund_amount' => $total,
                'status' => $total >= (float) $payment->amount - 0.01 ? 'refunded' : 'partially_refunded',
                'refund_reason' => 'Package cancelled by '.$staff.'.',
            ]);
            $refunded += $part;
            $remaining = round($remaining - $part, 2);
        }

        $stillOwed = round($amount - $refunded, 2);
        $reason = match (true) {
            $failed !== null => sprintf('Cancelled by our team. Refund of INR %s still to be paid manually.', number_format($stillOwed, 2, '.', '')),
            $amount > 0 => sprintf('Cancelled by our team. Refunded INR %s.', number_format($refunded, 2, '.', '')),
            default => 'Cancelled by our team, no refund under the package cancellation policy.',
        };

        $booking->update([
            'status' => 'cancelled',
            'cancellation_requested_at' => $booking->cancellation_requested_at ?? now(),
            'cancellation_reason' => $reason,
            'package_balance_amount' => 0,
            'admin_refund_amount' => $amount,
            'manual_refund_due_at' => $failed !== null ? now() : null,
            'admin_note' => $booking->adminNoteWith(sprintf('%s — %s cancelled the booking. Refund chosen: INR %s.%s',
                now()->format('j M Y, g:i A'), $staff, number_format($amount, 2),
                $failed !== null ? ' Razorpay refused the refund ('.$failed.'): refund INR '.number_format($stillOwed, 2).' in the Razorpay dashboard, then click "Record manual refund".' : '')),
        ]);

        return $failed !== null
            ? ['success' => false, 'message' => sprintf('Booking cancelled, but Razorpay could not refund INR %s. Refund it in the Razorpay dashboard, then click "Record manual refund".', number_format($stillOwed, 2))]
            : ['success' => true, 'message' => $amount > 0 ? sprintf('Booking cancelled and INR %s refunded to the guest.', number_format($refunded, 2)) : 'Booking cancelled with no refund.'];
    }

    /**
     * The team received the balance outside the website (bank transfer, UPI…).
     * Saved as an offline payment so "Guest has paid", the invoice and the
     * sales figures include it; it is never refunded through Razorpay.
     */
    public function markBalancePaidOffline(Booking $booking, string $staff, ?string $note, float $amount): void
    {
        $amount = round(max(0, $amount), 2);

        DB::transaction(function () use ($booking, $staff, $note, $amount) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->first();

            if ($amount > 0) {
                $booking->payments()->create([
                    'razorpay_order_id' => 'offline_'.$booking->reference.'_'.Str::lower(Str::random(8)),
                    'amount' => $amount,
                    'currency' => 'INR',
                    'status' => 'captured',
                    'method' => Payment::METHOD_OFFLINE,
                    'purpose' => self::PURPOSE_BALANCE,
                ]);
            }

            $booking->update([
                'package_balance_amount' => 0,
                'total_amount' => round((float) $booking->total_amount + $amount, 2),
                'admin_note' => $booking->adminNoteWith(sprintf('%s — %s marked the balance of INR %s as received offline.%s', now()->format('j M Y, g:i A'), $staff, number_format($amount, 2), $note ? ' '.trim($note) : '')),
            ]);
        });
    }

    /**
     * Balance still unpaid on the departure day: cancelled, booking amount
     * forfeited (client's rule). Returns how many bookings were cancelled.
     */
    public function forfeitUnpaid(): int
    {
        $count = 0;
        Booking::where('vertical', 'package')
            ->where('status', 'confirmed')
            ->whereNull('cancellation_requested_at')
            ->where('package_balance_amount', '>', 0)
            ->whereDate('check_in', '<=', today())
            ->each(function (Booking $booking) use (&$count) {
                $booking->update([
                    'status' => 'cancelled',
                    'cancellation_reason' => 'Balance not paid before departure — booking amount forfeited, no refund under the package policy.',
                    'admin_note' => $booking->adminNoteWith(now()->format('j M Y, g:i A').' — cancelled automatically: the balance of INR '.number_format((float) $booking->package_balance_amount, 2).' (before GST) was not paid before departure. The booking amount is forfeited.'),
                ]);
                $count++;
            });

        return $count;
    }
}
