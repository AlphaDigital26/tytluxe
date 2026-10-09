<?php

namespace Tests\Feature;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\FlightBookings\FlightBookingResource;
use App\Filament\Resources\FlightBookings\Pages\ListFlightBookings;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Booking permissions per admin role (agreed 2026-10-09) — see
 * BookingPolicy.
 */
class BookingRolesTest extends TestCase
{
    use RefreshDatabase;

    /** Logs in as a fresh admin with $role (session cleared: Filament's
     * AuthenticateSession logs out a session switched between admins). */
    protected function as(string $role): Admin
    {
        $this->flushSession();
        $admin = $this->admin($role);
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    protected function admin(string $role): Admin
    {
        return Admin::forceCreate(['name' => $role.' person', 'email' => uniqid('a').'@example.com', 'password' => bcrypt('x'), 'role' => $role, 'status' => 'Active']);
    }

    protected function flight(array $extra = []): Booking
    {
        $booking = Booking::forceCreate(array_merge([
            'reference' => 'TYT'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'user_id' => User::factory()->create()->id,
            'vertical' => 'flight',
            'status' => 'confirmed',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '9876543210',
            'tripjack_booking_id' => 'TJS'.random_int(100000000, 999999999),
            'flight_route' => 'DEL-BOM',
            'flight_departure_date' => now()->addDays(20)->toDateString(),
            'pax_adults' => 1, 'pax_children' => 0, 'pax_infants' => 0,
            'lead_guest_name' => 'Rahul Probe',
            'base_amount' => 5000, 'total_amount' => 5600, 'tripjack_total_price' => 5000,
            'currency' => 'INR',
            'manual_refund_due_at' => now(), // so "Record manual refund" can show
        ], $extra));
        Payment::create(['booking_id' => $booking->id, 'razorpay_order_id' => 'o_'.uniqid(), 'razorpay_payment_id' => 'p_'.uniqid(),
            'amount' => 5600, 'currency' => 'INR', 'status' => 'captured', 'purpose' => 'booking']);

        return $booking;
    }

    public function test_content_and_analyst_cannot_see_bookings(): void
    {
        foreach (['Content', 'Analyst'] as $role) {
            $this->as($role);
            $this->get(FlightBookingResource::getUrl('index'))->assertForbidden();
            $this->get(BookingResource::getUrl('index'))->assertForbidden();
        }

        foreach (['Super Admin', 'Operations', 'Support', 'Finance'] as $role) {
            $this->as($role);
            $this->get(FlightBookingResource::getUrl('index'))->assertOk();
            $this->get(BookingResource::getUrl('index'))->assertOk();
        }
    }

    public function test_each_role_gets_its_own_buttons(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $booking = $this->flight();
        $hold = $this->flight(['status' => 'on_hold', 'manual_refund_due_at' => null]);

        // [checkStatus, addNote, cancelFlight, releaseReservation, recordManualRefund]
        $matrix = [
            'Super Admin' => [true, true, true, true, true],
            'Operations' => [true, true, true, true, false],
            'Support' => [true, true, false, false, false],
            'Finance' => [true, true, false, false, true],
        ];

        foreach ($matrix as $role => [$check, $note, $cancel, $release, $refund]) {
            $this->as($role);
            $list = Livewire::test(ListFlightBookings::class);

            foreach (['checkStatus' => $check, 'addNote' => $note, 'cancelFlight' => $cancel, 'recordManualRefund' => $refund] as $action => $allowed) {
                $allowed ? $list->assertTableActionVisible($action, $booking) : $list->assertTableActionHidden($action, $booking);
            }
            $release ? $list->assertTableActionVisible('releaseReservation', $hold) : $list->assertTableActionHidden('releaseReservation', $hold);
        }
    }

    public function test_support_can_fix_contact_details_but_finance_cannot(): void
    {
        $booking = $this->flight(['vertical' => 'hotel']);

        $this->as('Support');
        $this->get(BookingResource::getUrl('edit', ['record' => $booking]))->assertOk();

        $this->as('Finance');
        $this->get(BookingResource::getUrl('edit', ['record' => $booking]))->assertForbidden();
    }
}
