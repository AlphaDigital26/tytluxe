<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Destination;
use App\Models\Hotel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TripJackBookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_review_book_confirmation_flow_with_pan_required(): void
    {
        $destination = Destination::create([
            'name' => 'Dubai', 'slug' => 'dubai', 'country' => 'UAE',
            'type' => 'city', 'for' => ['hotel'], 'is_active' => true,
        ]);

        $hotel = Hotel::create([
            'destination_id' => $destination->id,
            'title' => 'Test Sandbox Hotel',
            'slug' => 'test-sandbox-hotel-999',
            'description' => 'A hotel for flow testing.',
            'category' => 'city_luxury',
            'address' => 'Dubai, UAE',
            'star_rating' => 5,
            'price_from' => 0,
            'source' => 'tripjack',
            'tripjack_hotel_id' => '999999999',
            'is_active' => true,
        ]);

        Http::fake([
            '*/hotel/pricing' => Http::response([
                'tjHotelId' => '999999999',
                'hotelName' => 'Test Sandbox Hotel',
                'options' => [[
                    'optionId' => 'opt-abc-123',
                    'optionType' => 'SRSM',
                    'roomInfo' => [['id' => 'r1', 'name' => 'Deluxe King Room']],
                    'inclusions' => ['Free Wi-Fi'],
                    'mealBasis' => 'Breakfast',
                    'pricing' => ['totalPrice' => 25000, 'basePrice' => 20000, 'discount' => 0, 'taxes' => 4500, 'mf' => 60, 'mft' => 10.8, 'currency' => 'INR'],
                    'commercial' => ['type' => 'NET', 'commission' => 0],
                    'compliance' => ['gstType' => 'NA', 'panRequired' => true, 'passportRequired' => false],
                    'cancellation' => ['isRefundable' => true, 'penalties' => [['from' => '2026-01-01T00:00:00', 'to' => '2026-09-10T23:59:59', 'amount' => 0]]],
                ]],
                'reviewHash' => 'fakehash123',
                'status' => ['success' => true],
                'correlationId' => 'test-cid',
            ], 200),

            '*/hotel/review' => Http::response([
                'correlationId' => 'test-cid',
                'tjHotelId' => '999999999',
                'hotelName' => 'Test Sandbox Hotel',
                'bookingId' => 'TGS-REVIEW-123',
                'option' => [
                    'optionId' => 'opt-abc-123',
                    'optionType' => 'SRSM',
                    'roomInfo' => [['id' => 'r1', 'name' => 'Deluxe King Room']],
                    'inclusions' => ['Free Wi-Fi'],
                    'mealBasis' => 'Breakfast',
                    'pricing' => ['totalPrice' => 25000, 'basePrice' => 20000, 'discount' => 0, 'taxes' => 4500, 'mf' => 60, 'mft' => 10.8, 'currency' => 'INR'],
                    'commercial' => ['type' => 'NET', 'commission' => 0],
                    'compliance' => ['gstType' => 'NA', 'panRequired' => true, 'passportRequired' => false],
                    'cancellation' => ['isRefundable' => true, 'penalties' => []],
                    'deadlineDateTime' => '2026-09-14T23:59:59',
                ],
                'onholdAllowed' => 'true',
                'status' => ['success' => true],
            ], 200),

            '*/hotel/book' => Http::response([
                'bookingId' => 'TJ-BOOK-456',
                'status' => ['success' => true],
                'metaInfo' => [],
            ], 200),

            '*/hotel/booking-details' => Http::response([
                'order' => ['bookingId' => 'TJ-BOOK-456', 'amount' => 25000, 'status' => 'ON_HOLD', 'createdOn' => '2026-08-27T10:00:00'],
                'itemInfos' => ['HOTEL' => ['hInfo' => ['name' => 'Test Sandbox Hotel']]],
                'status' => ['success' => true, 'httpStatus' => 200],
            ], 200),
        ]);

        // Step 1: visit hotel details with dates -> populates session pricing context
        $detailsResponse = $this->get("/hotels/{$hotel->slug}?check_in=2026-09-15&check_out=2026-09-18&adults=1&rooms=1");
        $detailsResponse->assertStatus(200);
        $detailsResponse->assertSee('Select Room');
        $detailsResponse->assertSee('INR 25,000');

        // Step 2: submit Select Room -> Review (should redirect, not render directly — PRG)
        $reviewPost = $this->post("/hotels/{$hotel->slug}/review", [
            'option_id' => 'opt-abc-123',
            'check_in' => '2026-09-15',
            'check_out' => '2026-09-18',
            'adults' => 1,
            'children' => 0,
            'rooms' => 1,
        ]);
        $reviewPost->assertRedirect("/hotels/{$hotel->slug}/review");

        // Step 3: follow to the GET review page — should show guest form with PAN field (compliance said panRequired=true)
        $reviewGet = $this->get("/hotels/{$hotel->slug}/review");
        $reviewGet->assertStatus(200);
        $reviewGet->assertSee('PAN Number');
        $reviewGet->assertSee('Deluxe King Room');
        $reviewGet->assertSee('25,000');

        // Step 4: submit guest details -> Book
        $bookPost = $this->post("/hotels/{$hotel->slug}/book", [
            'lead_name' => 'John Doe',
            'lead_email' => 'john@example.com',
            'lead_phone' => '9876543210',
            'pan_number' => 'ABCDE1234F',
            'rooms' => [
                0 => ['travelers' => [
                    0 => ['title' => 'Mr', 'first_name' => 'John', 'last_name' => 'Doe'],
                ]],
            ],
        ]);

        $booking = Booking::first();
        $this->assertNotNull($booking, 'Booking row should have been created after successful Book API call');
        $bookPost->assertRedirect("/booking/{$booking->reference}");

        $this->assertSame('TJ-BOOK-456', $booking->tripjack_booking_id);
        $this->assertSame('TGS-REVIEW-123', $booking->tripjack_hold_id);
        $this->assertSame('opt-abc-123', $booking->tripjack_option_id);
        $this->assertSame('pending_payment', $booking->status); // ON_HOLD maps to pending_payment (awaiting Phase 8 payment)
        $this->assertSame(25000.0, (float) $booking->total_amount);
        $this->assertSame(1, $booking->travelers()->count());
        $this->assertSame('ABCDE1234F', $booking->travelers()->first()->pan_number);

        // Step 5: confirmation page shows live status
        $confirmation = $this->get("/booking/{$booking->reference}");
        $confirmation->assertStatus(200);
        $confirmation->assertSee('ON_HOLD');
        $confirmation->assertSee($booking->reference);
    }

    public function test_duplicate_lead_guest_names_across_rooms_are_rejected(): void
    {
        $destination = Destination::create([
            'name' => 'Dubai', 'slug' => 'dubai-2', 'country' => 'UAE',
            'type' => 'city', 'for' => ['hotel'], 'is_active' => true,
        ]);
        $hotel = Hotel::create([
            'destination_id' => $destination->id,
            'title' => 'Two Room Hotel', 'slug' => 'two-room-hotel-998',
            'description' => 'x', 'category' => 'city_luxury', 'address' => 'Dubai',
            'star_rating' => 4, 'price_from' => 0, 'source' => 'tripjack',
            'tripjack_hotel_id' => '999999998', 'is_active' => true,
        ]);

        session(['tripjack_booking_draft' => [
            'hotel_id' => $hotel->id,
            'hid' => '999999998',
            'bookingId' => 'TGS-X',
            'option' => ['optionId' => 'opt-x', 'compliance' => ['panRequired' => false, 'passportRequired' => false], 'pricing' => ['totalPrice' => 1000, 'currency' => 'INR']],
            'correlationId' => 'cid-x',
            'check_in' => '2026-09-15', 'check_out' => '2026-09-18',
            'adults' => 2, 'children' => 0, 'rooms' => 2,
        ]]);

        $response = $this->post("/hotels/{$hotel->slug}/book", [
            'lead_name' => 'Jane Doe', 'lead_email' => 'jane@example.com', 'lead_phone' => '9876543210',
            'rooms' => [
                0 => ['travelers' => [0 => ['title' => 'Mr', 'first_name' => 'John', 'last_name' => 'Doe']]],
                1 => ['travelers' => [0 => ['title' => 'Mr', 'first_name' => 'John', 'last_name' => 'Doe']]],
            ],
        ]);

        $response->assertSessionHasErrors('rooms');
        $this->assertSame(0, Booking::count(), 'No booking should be created when lead names collide across rooms');
    }

    public function test_onhold_not_allowed_option_is_rejected_before_guest_form(): void
    {
        $destination = Destination::create([
            'name' => 'Dubai', 'slug' => 'dubai-3', 'country' => 'UAE',
            'type' => 'city', 'for' => ['hotel'], 'is_active' => true,
        ]);
        $hotel = Hotel::create([
            'destination_id' => $destination->id,
            'title' => 'No Hold Hotel', 'slug' => 'no-hold-hotel-997',
            'description' => 'x', 'category' => 'city_luxury', 'address' => 'Dubai',
            'star_rating' => 4, 'price_from' => 0, 'source' => 'tripjack',
            'tripjack_hotel_id' => '999999997', 'is_active' => true,
        ]);

        session(['tripjack_pricing.999999997' => [
            'correlationId' => 'cid-y', 'reviewHash' => 'hash-y', 'hid' => '999999997',
            'check_in' => '2026-09-15', 'check_out' => '2026-09-18', 'adults' => 2, 'children' => 0, 'rooms' => 1,
        ]]);

        Http::fake([
            '*/hotel/review' => Http::response([
                'bookingId' => 'TGS-NOHOLD',
                'option' => ['optionId' => 'opt-nohold', 'pricing' => ['totalPrice' => 1000, 'currency' => 'INR']],
                'onholdAllowed' => 'false',
                'status' => ['success' => true],
            ], 200),
        ]);

        $response = $this->post("/hotels/{$hotel->slug}/review", [
            'option_id' => 'opt-nohold', 'check_in' => '2026-09-15', 'check_out' => '2026-09-18',
            'adults' => 2, 'children' => 0, 'rooms' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('booking_error');
        $this->assertStringContainsString("can't be held", session('booking_error'));
        $this->assertNull(session('tripjack_booking_draft'), 'No draft should be stored for a non-holdable option');
    }

    public function test_real_child_ages_are_sent_to_tripjack_not_hardcoded(): void
    {
        $destination = Destination::create([
            'name' => 'Dubai', 'slug' => 'dubai-4', 'country' => 'UAE',
            'type' => 'city', 'for' => ['hotel'], 'is_active' => true,
        ]);
        $hotel = Hotel::create([
            'destination_id' => $destination->id,
            'title' => 'Family Hotel', 'slug' => 'family-hotel-996',
            'description' => 'x', 'category' => 'city_luxury', 'address' => 'Dubai',
            'star_rating' => 4, 'price_from' => 0, 'source' => 'tripjack',
            'tripjack_hotel_id' => '999999996', 'is_active' => true,
        ]);

        Http::fake([
            '*/hotel/pricing' => Http::response([
                'tjHotelId' => '999999996', 'options' => [], 'reviewHash' => 'h', 'status' => ['success' => true],
            ], 200),
        ]);

        $this->get("/hotels/{$hotel->slug}?check_in=2026-09-15&check_out=2026-09-18&adults=1&children=2&rooms=1&child_ages=5,9");

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/hotel/pricing')) {
                return true;
            }
            $ages = $request->data()['rooms'][0]['childAge'] ?? null;

            return $ages === [5, 9];
        });
    }

    public function test_guest_form_trusts_reviewed_roomInfo_counts_over_stale_session_counts(): void
    {
        $destination = Destination::create([
            'name' => 'Dubai', 'slug' => 'dubai-5', 'country' => 'UAE',
            'type' => 'city', 'for' => ['hotel'], 'is_active' => true,
        ]);
        $hotel = Hotel::create([
            'destination_id' => $destination->id,
            'title' => 'Mismatch Hotel', 'slug' => 'mismatch-hotel-995',
            'description' => 'x', 'category' => 'city_luxury', 'address' => 'Dubai',
            'star_rating' => 4, 'price_from' => 0, 'source' => 'tripjack',
            'tripjack_hotel_id' => '999999995', 'is_active' => true,
        ]);

        // Session says 1 adult, but the reviewed option's roomInfo (what
        // TripJack actually confirmed) says 2 adults for this room — the
        // guest form must render 2 traveler slots, trusting roomInfo.
        session(['tripjack_booking_draft' => [
            'hotel_id' => $hotel->id,
            'hid' => '999999995',
            'bookingId' => 'TGS-MISMATCH',
            'option' => [
                'optionId' => 'opt-mismatch',
                'roomInfo' => [['id' => 'r1', 'name' => 'Twin Room', 'adults' => 2, 'children' => 0]],
                'compliance' => ['panRequired' => false, 'passportRequired' => false],
                'pricing' => ['totalPrice' => 2000, 'currency' => 'INR'],
            ],
            'correlationId' => 'cid-mismatch',
            'check_in' => '2026-09-15', 'check_out' => '2026-09-18',
            'adults' => 1, 'children' => 0, 'rooms' => 1, // stale/mismatched on purpose
        ]]);

        $response = $this->get("/hotels/{$hotel->slug}/review");

        $response->assertStatus(200);
        $response->assertSee('Adult 1');
        $response->assertSee('Adult 2'); // proves 2 slots rendered, not 1
    }
}
