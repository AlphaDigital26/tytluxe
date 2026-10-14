<?php

namespace App\Console\Commands;

use App\Mail\BookingUpdateMail;
use App\Models\Booking;
use App\Services\PackageBookingService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * Group package balances: reminds guests REMIND_DAYS_BEFORE days before the
 * balance falls due and again on the due date, then cancels bookings still
 * unpaid on the departure day (booking amount forfeited — client's rule).
 */
class PackageBalanceCheck extends Command
{
    protected $signature = 'packages:balance-check';

    protected $description = 'Send package balance reminders and cancel bookings whose balance was not paid before departure';

    public const REMIND_DAYS_BEFORE = 3;

    public function handle(PackageBookingService $service): int
    {
        $reminded = 0;
        Booking::where('vertical', 'package')
            ->where('status', 'confirmed')
            ->whereNull('cancellation_requested_at')
            ->where('package_balance_amount', '>', 0)
            ->whereDate('package_balance_due_on', '<=', today()->addDays(self::REMIND_DAYS_BEFORE))
            ->whereDate('check_in', '>', today())
            ->each(function (Booking $booking) use (&$reminded) {
                $due = Carbon::parse($booking->package_balance_due_on);
                $last = $booking->package_balance_reminded_at;
                // First reminder, then one more once the due date arrives.
                if ($last !== null && ! (today()->gte($due) && $last->lt($due))) {
                    return;
                }
                if (blank($booking->guest_email)) {
                    return;
                }

                $mail = Mail::to($booking->guest_email);
                if (filled($team = config('services.booking_emails.team_copy')) && strcasecmp($team, $booking->guest_email) !== 0) {
                    $mail->bcc($team);
                }
                $mail->queue(new BookingUpdateMail($booking, BookingUpdateMail::BALANCE_REMINDER));
                $booking->update(['package_balance_reminded_at' => now()]);
                $reminded++;
            });

        $forfeited = $service->forfeitUnpaid();

        $this->info("Reminders sent: {$reminded}. Unpaid bookings cancelled: {$forfeited}.");

        return self::SUCCESS;
    }
}
