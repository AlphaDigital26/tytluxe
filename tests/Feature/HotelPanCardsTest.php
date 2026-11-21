<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Doubles\FakeRazorpayService;
use Tests\TestCase;

/** Hotel PANs as on TripJack's own form: several PANs, each covering chosen guests. */
class HotelPanCardsTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        $this->app->instance(\App\Services\Payment\RazorpayService::class, new FakeRazorpayService());
        $this->actingAs(User::factory()->create());
        $destination = Destination::create(['name' => 'Jaipur', 'slug' => 'jaipur-pan', 'country' => 'India', 'type' => 'city', 'for' => ['hotel'], 'is_active' => true]);
        $this->hotel = Hotel::create([
            'destination_id' => $destination->id, 'title' => 'Rambagh Test', 'slug' => 'rambagh-pan', 'description' => 'x',
            'category' => 'city_luxury', 'address' => 'Jaipur', 'star_rating' => 5, 'price_from' => 0,
            'source' => 'tripjack', 'tripjack_hotel_id' => '777777001', 'is_active' => true,
        ]);
        // Two rooms, two adults each.
        session(['tripjack_booking_draft' => [
            'hotel_id' => $this->hotel->id, 'hid' => '777777001', 'bookingId' => 'TGS-PAN-1',
            'option' => [
                'optionId' => 'opt-pan', 'roomInfo' => [['id' => 'r1', 'name' => 'Rajputana Suite', 'adults' => 2], ['id' => 'r2', 'name' => 'Rajputana Suite', 'adults' => 2]],
                'compliance' => ['panRequired' => true, 'passportRequired' => false],
                'pricing' => ['totalPrice' => 61277, 'basePrice' => 60000, 'currency' => 'INR', 'customerPrice' => 70000,
                    'pricingBreakdown' => ['tripjack_total_price' => 61277, 'gst_slab' => 0.18, 'margin_amount' => 5000, 'gst_on_margin' => 900, 'razorpay_recovery' => 1700]],
            ],
            'correlationId' => 'cid-pan', 'check_in' => now()->addDays(30)->toDateString(), 'check_out' => now()->addDays(32)->toDateString(),
            'adults' => 4, 'children' => 0, 'rooms' => 2,
        ]]);
    }

    protected function form(array $over = []): array
    {
        return array_replace_recursive([
            'contact_email' => 'ankita.kumari@gmail.com', 'contact_phone' => '9876501234',
            'rooms' => [
                0 => ['travelers' => [0 => ['title' => 'Ms', 'first_name' => 'Ankita', 'last_name' => 'Kumari'], 1 => ['title' => 'Mr', 'first_name' => 'Rohit', 'last_name' => 'Kumar']]],
                1 => ['travelers' => [0 => ['title' => 'Ms', 'first_name' => 'Jyoti', 'last_name' => 'Pal'], 1 => ['title' => 'Mr', 'first_name' => 'Amit', 'last_name' => 'Pal']]],
            ],
        ], $over);
    }

    protected function payload(): array
    {
        return Booking::firstOrFail()->tripjack_room_traveller_payload;
    }

    public function test_each_pan_goes_only_on_the_guests_chosen_for_it(): void
    {
        $this->post('/hotels/rambagh-pan/book', $this->form(['pan_type' => 'personal', 'pans' => [
            ['name' => 'Ankita Kumari', 'number' => 'abcpk1234f', 'guests' => ['0-0', '0-1']],
            ['name' => 'Jyoti Pal', 'number' => 'BCDPJ2345G', 'guests' => ['1-0']],
        ]]))->assertSessionHasNoErrors();

        $rooms = $this->payload();
        $this->assertSame('ABCPK1234F', $rooms[0]['travellerInfo'][0]['pan']); // typed in lower case, sent in capitals
        $this->assertSame('ABCPK1234F', $rooms[0]['travellerInfo'][1]['pan']); // shared PAN in room 1
        $this->assertSame('BCDPJ2345G', $rooms[1]['travellerInfo'][0]['pan']);
        $this->assertSame('Jyoti Pal', $rooms[1]['travellerInfo'][0]['panName']);
        $this->assertArrayNotHasKey('pan', $rooms[1]['travellerInfo'][1]); // not chosen on any PAN
    }

    public function test_every_rooms_lead_guest_needs_a_pan(): void
    {
        $this->post('/hotels/rambagh-pan/book', $this->form(['pans' => [
            ['name' => 'Ankita Kumari', 'number' => 'ABCPK1234F', 'guests' => ['0-0']],
        ]]))->assertSessionHasErrors(['pans' => 'Room 2: please add a PAN for the lead guest, or choose them on an existing PAN.']);
        $this->assertSame(0, Booking::count());
    }

    public function test_a_guest_cannot_be_on_two_different_pans(): void
    {
        $this->post('/hotels/rambagh-pan/book', $this->form(['pans' => [
            ['name' => 'Ankita Kumari', 'number' => 'ABCPK1234F', 'guests' => ['0-0', '1-0']],
            ['name' => 'Jyoti Pal', 'number' => 'BCDPJ2345G', 'guests' => ['1-0']],
        ]]))->assertSessionHasErrors('pans');
    }

    public function test_corporate_pan_covers_every_guest_and_must_be_a_company_pan(): void
    {
        $this->post('/hotels/rambagh-pan/book', $this->form(['pan_type' => 'corporate', 'pans' => [
            ['name' => 'Take Your Trip', 'number' => 'ABCPK1234F'],
        ]]))->assertSessionHasErrors('pans'); // a personal PAN (4th letter P)

        $this->post('/hotels/rambagh-pan/book', $this->form(['pan_type' => 'corporate', 'pans' => [
            ['name' => 'Take Your Trip', 'number' => 'AAJCT7359P'],
        ]]))->assertSessionHasNoErrors();
        $pans = collect($this->payload())->flatMap(fn ($r) => array_column($r['travellerInfo'], 'pan'))->unique()->values()->all();
        $this->assertSame(['AAJCT7359P'], $pans);
        $this->assertSame(4, collect($this->payload())->sum(fn ($r) => count($r['travellerInfo'])));
    }

    public function test_the_page_starts_with_one_pan_covering_every_guest(): void
    {
        $page = $this->get('/hotels/rambagh-pan/review')->assertOk();
        $page->assertSee('Personal PAN')->assertSee('Corporate PAN')->assertSee('+ Add PAN');
        $html = $page->getContent();
        $this->assertSame(1, preg_match_all('/class="br-pan-card" data-k="[0-9]+"/', $html));
        foreach (['0-0', '0-1', '1-0', '1-1'] as $guest) {
            $this->assertMatchesRegularExpression('/name="pans\[0\]\[guests\]\[\]" value="'.$guest.'"\s+checked/', $html);
        }
        $this->assertStringContainsString('data-guests="4"', $html); // Add PAN stops at 4 PANs
    }

    public function test_one_pan_for_both_rooms_goes_to_every_guest(): void
    {
        // TripJack cases 10 and 11: the same PAN across rooms / for everyone in a room.
        $this->post('/hotels/rambagh-pan/book', $this->form(['pans' => [
            ['name' => 'Ankita Kumari', 'number' => 'ABCPK1234F', 'guests' => ['0-0', '0-1', '1-0', '1-1']],
        ]]))->assertSessionHasNoErrors();
        $pans = collect($this->payload())->flatMap(fn ($r) => array_column($r['travellerInfo'], 'pan'))->all();
        $this->assertSame(['ABCPK1234F', 'ABCPK1234F', 'ABCPK1234F', 'ABCPK1234F'], $pans);
    }

    public function test_no_more_pans_than_guests_and_no_pan_without_guests(): void
    {
        $five = array_map(fn ($i) => ['name' => 'Ankita Kumari', 'number' => 'ABCPK1234F', 'guests' => [$i < 4 ? intdiv($i, 2).'-'.($i % 2) : '0-0']], range(0, 4));
        $this->post('/hotels/rambagh-pan/book', $this->form(['pans' => $five]))
            ->assertSessionHasErrors(['pans' => 'There are 4 guests, so add at most 4 PANs.']);

        $this->post('/hotels/rambagh-pan/book', $this->form(['pans' => [
            ['name' => 'Ankita Kumari', 'number' => 'ABCPK1234F', 'guests' => ['0-0', '1-0']],
            ['name' => 'Rohit Kumar', 'number' => 'BCDPK2345G'],
        ]]))->assertSessionHasErrors(['pans' => 'Choose at least one guest for each PAN, or remove the PAN.']);
        $this->assertSame(0, Booking::count());
    }
}
