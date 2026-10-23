<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Hotel;
use App\Models\User;
use App\Services\TripJack\TripJackClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/** Fixes from the TripJack hotel self-certification test (21 Oct 2026). */
class HotelCertificationFixesTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $hotel;

    private string $checkIn;

    private string $checkOut;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkIn = now()->addDays(20)->toDateString();
        $this->checkOut = now()->addDays(22)->toDateString();
        $this->actingAs(User::factory()->create());

        $destination = Destination::create(['name' => 'Dubai', 'slug' => 'dubai', 'country' => 'UAE', 'type' => 'city', 'for' => ['hotel'], 'is_active' => true]);
        $this->hotel = Hotel::create([
            'destination_id' => $destination->id, 'title' => 'Cert Hotel', 'slug' => 'cert-hotel-1',
            'description' => 'x', 'category' => 'city_luxury', 'address' => 'Dubai', 'star_rating' => 5,
            'price_from' => 0, 'source' => 'tripjack', 'tripjack_hotel_id' => '900000001', 'is_active' => true,
        ]);
    }

    private function option(string $id, string $room, ?array $inventory = null): array
    {
        return array_filter([
            'optionId' => $id, 'optionType' => 'SRSM',
            'roomInfo' => [['id' => 'r-'.$id, 'name' => $room]],
            'mealBasis' => 'Room Only',
            'pricing' => ['totalPrice' => 10000, 'basePrice' => 9000, 'currency' => 'INR'],
            'compliance' => ['gstType' => 'NA', 'panRequired' => false, 'passportRequired' => false],
            'cancellation' => ['isRefundable' => false, 'penalties' => []],
            'inventory' => $inventory,
        ], fn ($v) => $v !== null);
    }

    private function url(): string
    {
        return "/hotels/{$this->hotel->slug}?check_in={$this->checkIn}&check_out={$this->checkOut}&adults=2&children=0&rooms=1";
    }

    public function test_options_with_no_inventory_are_not_shown(): void
    {
        Http::fake(['*/hotel/pricing' => Http::response(['options' => [
            $this->option('o-ok', 'Ocean Suite', ['available' => true]),
            $this->option('o-gone', 'Sold Out Villa', ['available' => false]),
            $this->option('o-plain', 'City Room'),
        ], 'reviewHash' => 'h', 'status' => ['success' => true]])]);

        $this->get($this->url())
            ->assertSee('Ocean Suite')->assertSee('City Room')
            ->assertDontSee('Sold Out Villa')->assertDontSee('o-gone');
    }

    public function test_an_expired_search_id_is_never_reused(): void
    {
        Http::fake(['*/hotel/pricing' => Http::response(['options' => [], 'reviewHash' => 'h', 'status' => ['success' => true]])]);
        $search = ['correlationId' => 'old-cid', 'destination_id' => $this->hotel->destination_id, 'check_in' => $this->checkIn,
            'check_out' => $this->checkOut, 'adults' => 2, 'children' => 0, 'rooms' => 1, 'nationality' => '106', 'child_ages' => []];

        // Fresh (1 minute old): the hotel page continues the same search.
        $this->withSession(['tripjack_search' => $search + ['searched_at' => now()->subMinute()->timestamp]])->get($this->url());
        Http::assertSent(fn ($r) => str_contains($r->url(), '/hotel/pricing') && $r->data()['correlationId'] === 'old-cid');

        // 13 minutes old: a new search id, as TripJack requires.
        Http::fake(['*/hotel/pricing' => Http::response(['options' => [], 'reviewHash' => 'h', 'status' => ['success' => true]])]);
        $this->withSession(['tripjack_search' => $search + ['searched_at' => now()->subMinutes(13)->timestamp]])->get($this->url());
        Http::assertSent(fn ($r) => str_contains($r->url(), '/hotel/pricing') && $r->data()['correlationId'] !== 'old-cid');
    }

    public function test_hotel_page_shows_a_price_countdown_and_a_refresh_prompt(): void
    {
        Http::fake(['*/hotel/pricing' => Http::response(['options' => [$this->option('o-1', 'Ocean Suite')], 'reviewHash' => 'h', 'status' => ['success' => true]])]);

        $this->get($this->url())
            ->assertSee('id="hdPriceTimer"', false)
            ->assertSee('Prices held for')
            ->assertSee('Refresh prices');
    }

    public function test_a_booking_over_the_payment_limit_returns_to_the_guest_form_with_a_clear_message(): void
    {
        $option = $this->option('o-big', 'Presidential Suite');
        Http::fake([
            '*/hotel/pricing' => Http::response(['options' => [$option], 'reviewHash' => 'h', 'status' => ['success' => true]]),
            '*/hotel/review' => Http::response(['bookingId' => 'TGS-BIG-1', 'option' => $option, 'status' => ['success' => true]]),
        ]);
        $this->app->bind(\App\Services\Payment\RazorpayService::class, fn () => new class extends \Tests\Doubles\FakeRazorpayService
        {
            public function createOrder(float $amountRupees, string $receipt): array
            {
                throw new \App\Services\Payment\PaymentOrderException(\App\Services\Payment\PaymentOrderException::OVER_LIMIT_MESSAGE);
            }
        });

        $this->get($this->url());
        $this->post("/hotels/{$this->hotel->slug}/review", ['option_id' => 'o-big']);
        $this->post("/hotels/{$this->hotel->slug}/book", [
            'contact_email' => 'guest@gmail.com', 'contact_phone' => '9876543210',
            'rooms' => [['travelers' => [['title' => 'Mr', 'first_name' => 'Ravi', 'last_name' => 'Kumar'], ['title' => 'Mrs', 'first_name' => 'Asha', 'last_name' => 'Kumar']]]],
        ])->assertRedirect("/hotels/{$this->hotel->slug}/review")
            ->assertSessionHas('booking_error', \App\Services\Payment\PaymentOrderException::OVER_LIMIT_MESSAGE);

        // No unpaid booking left behind, and the guest's choice is kept.
        $this->assertSame(0, \App\Models\Booking::count());
        $this->assertNotNull(session('tripjack_booking_draft'));
    }

    public function test_duplicate_booking_error_has_a_clear_message(): void
    {
        $described = \App\Services\TripJack\TripJackErrorCatalog::describe('2631');
        $this->assertStringContainsString('already have a booking', $described['message']);
    }

    public function test_pan_and_passport_numbers_are_masked_in_logs(): void
    {
        $masked = TripJackClient::maskIdentityNumbers(['bookingId' => 'TGS1', 'roomTravellerInfo' => [['travellerInfo' => [
            ['fN' => 'Arjun', 'pan' => 'ABCPS1234K', 'pNum' => 'P1234567'],
        ]]]]);

        $t = $masked['roomTravellerInfo'][0]['travellerInfo'][0];
        $this->assertSame('******234K', $t['pan']);
        $this->assertSame('****4567', $t['pNum']);
        $this->assertSame('Arjun', $t['fN']);
        $this->assertSame('TGS1', $masked['bookingId']);
    }
}
