<?php

namespace Tests\Feature;

use App\Support\UatRecorder;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/** TripJack UAT logs, laid out exactly like TripJack's own sample logs. */
class UatRecorderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // A throwaway folder, never the real storage/uat holding your UAT logs.
        config(['services.tripjack.env' => 'test', 'services.tripjack.uat_path' => sys_get_temp_dir().'/tyt-uat-test-'.uniqid()]);
    }

    protected function tearDown(): void
    {
        UatRecorder::stop();
        File::deleteDirectory(UatRecorder::root());
        parent::tearDown();
    }

    public function test_flight_calls_are_saved_with_tripjacks_file_names(): void
    {
        UatRecorder::start('flight', 'Tripjack API logs/oneway/DEL-BOM-1A-DIRECT');

        UatRecorder::record('flight', '/fms/v1/air-search-all', ['searchQuery' => ['cabinClass' => 'ECONOMY']], '{"searchResult":{}}');
        UatRecorder::record('flight', '/fms/v1/review', ['priceIds' => ['P1']], '{"bookingId":"TJS1"}');
        UatRecorder::record('flight', '/oms/v1/air/book', ['bookingId' => 'TJS1'], '{"bookingId":"TJS1"}');
        UatRecorder::record('flight', '/oms/v1/booking-details', ['bookingId' => 'TJS1'], '{"order":{}}');
        UatRecorder::record('hotel', '/hotel/book', ['x' => 1], '{}'); // not the open case's vertical

        $dir = UatRecorder::root().'/Tripjack API logs/oneway/DEL-BOM-1A-DIRECT';
        $this->assertSame([
            'BookingDetailRequest.json', 'BookingDetailResponse.json', 'BookingRequest.json', 'BookingResponse.json',
            'ReviewRequest.json', 'ReviewResponse.json', 'SearchRequest.json', 'SearchResponse.json',
        ], collect(File::files($dir))->map->getFilename()->sort()->values()->all());
        // The request file is the request body only, as in TripJack's samples.
        $this->assertSame(['searchQuery' => ['cabinClass' => 'ECONOMY']], json_decode(File::get("$dir/SearchRequest.json"), true));
    }

    public function test_cab_calls_are_saved_one_file_per_request_and_response(): void
    {
        UatRecorder::start('cab', 'CAB API TEST CASES/Airport Transfer - One Way');
        $dir = UatRecorder::root().'/CAB API TEST CASES/Airport Transfer - One Way';

        UatRecorder::record('cab', '/cabs/v1/get-lat-long', ['placeId' => 'PICKUP'], '{"success":true}');
        UatRecorder::record('cab', '/cabs/v1/get-lat-long', ['placeId' => 'DROP'], '{"success":true}');
        UatRecorder::record('cab', '/cabs/v2/quotes', ['passengers' => 3], '{"success":true}');
        UatRecorder::record('cab', '/cabs/v2/booking', ['consent' => 'yes'], '{"success":true}');
        UatRecorder::record('cab', '/cabs/v1/payment/create', ['opType' => 'DEBIT'], '{"success":true}');
        UatRecorder::record('cab', '/cabs/v1/booking/details', ['bookingIds' => 'TJS1'], '{"success":true}');
        UatRecorder::record('cab', '/cabs/v1/amendment', ['bookingId' => 'TJS1', 'type' => 'CANCELLATION'], '{"success":true}');
        UatRecorder::record('cab', '/cabs/v1/amendment', ['bookingId' => 'TJS1', 'amendmentType' => 'CANCELLATION'], '{"success":true}');

        $this->assertSame([
            'BookingDetailsRequest.json', 'BookingDetailsResponse.json', 'BookingRequest.json', 'BookingResponse.json',
            'CancellationChargesRequest.json', 'CancellationChargesResponse.json', 'CancellationRequest.json', 'CancellationResponse.json',
            'LatLong2Request.json', 'LatLong2Response.json', 'LatLongRequest.json', 'LatLongResponse.json',
            'PaymentRequest.json', 'PaymentResponse.json', 'QuotesRequest.json', 'QuotesResponse.json',
        ], collect(File::files($dir))->map->getFilename()->sort()->values()->all());
        $this->assertSame(['placeId' => 'DROP'], json_decode(File::get("$dir/LatLong2Request.json"), true));
    }

    public function test_hotel_hold_book_cancel_and_detail_after_cancel(): void
    {
        UatRecorder::start('hotel', 'HOTEL API TEST CASES/Test Case 6');
        $dir = UatRecorder::root().'/HOTEL API TEST CASES/Test Case 6';

        UatRecorder::record('hotel', '/hotel/listing', ['hids' => [1]], '{"hotels":[]}'); // empty batch: skipped
        UatRecorder::record('hotel', '/hotel/listing', ['hids' => [2]], '{"hotels":[{"id":2}]}');
        UatRecorder::record('hotel', '/hotel/listing', ['hids' => [3]], '{"hotels":[{"id":3}]}'); // first one kept
        UatRecorder::record('hotel', '/hotel/review', ['optionId' => 'O1'], '{"option":{"cancellation":{"isRefundable":true}}}');
        UatRecorder::record('hotel', '/hotel/book', ['bookingId' => 'H1'], '{"bookingId":"H1"}'); // no paymentInfos = hold
        UatRecorder::record('hotel', '/hotel/booking-details', ['bookingId' => 'H1'], '{"order":{"status":"ON_HOLD"}}');
        UatRecorder::record('hotel', '/hotel/cancel-booking/H1', [], '{"status":{"success":true}}');
        UatRecorder::record('hotel', '/hotel/booking-details', ['bookingId' => 'H1'], '{"order":{"status":"CANCELLED"}}');

        $this->assertSame(['hids' => [2]], json_decode(File::get("$dir/TJ test Hotel Search Request.json"), true));
        $this->assertSame(['isRefundable' => true], json_decode(File::get("$dir/TJ test Hotel cancellation policy Response.json"), true));
        $this->assertFileExists("$dir/TJ test Hotel Hold Request.json");
        $this->assertFileExists("$dir/TJ Test Hotel Cancel Response.json");
        $this->assertStringContainsString('ON_HOLD', File::get("$dir/TJ test hotel booking detail Response.json"));
        $this->assertStringContainsString('CANCELLED', File::get("$dir/TJ test hotel detail after cancel Response.json"));

        UatRecorder::record('hotel', '/hotel/book', ['bookingId' => 'H2', 'paymentInfos' => [['amount' => 1]]], '{}');
        $this->assertFileExists("$dir/TJ test Hotel Book Request.json");
    }

    public function test_hotel_v3_review_policy_is_saved_from_option_cancellation(): void
    {
        UatRecorder::start('hotel', 'HOTEL API TEST CASES/Test Case 9');
        $dir = UatRecorder::root().'/HOTEL API TEST CASES/Test Case 9';

        UatRecorder::record('hotel', '/hotel/review', ['optionId' => 'O1'],
            '{"bookingId":"TJP1","option":{"cancellation":{"isRefundable":true,"penalties":[{"amount":0}]}}}');

        $this->assertSame(['isRefundable' => true, 'penalties' => [['amount' => 0]]],
            json_decode(File::get("$dir/TJ test Hotel cancellation policy Response.json"), true));
    }

    /** TripJack rejected a case whose Search files came from a batch without the priced hotel. */
    public function test_search_files_are_the_batch_holding_the_priced_hotel(): void
    {
        UatRecorder::start('hotel', 'HOTEL API TEST CASES/Test Case 4');
        $dir = UatRecorder::root().'/HOTEL API TEST CASES/Test Case 4';

        UatRecorder::record('hotel', '/hotel/listing', ['hids' => [100000000055, 100000000150]], '{"hotels":[{"hotelId":"100000000055"}]}');
        UatRecorder::record('hotel', '/hotel/listing', ['hids' => [100000137195, 100000137200]], '{"hotels":[{"hotelId":"100000137195"}]}');
        // Before pricing, the first batch with hotels stands in.
        $this->assertSame([100000000055, 100000000150], json_decode(File::get("$dir/TJ test Hotel Search Request.json"), true)['hids']);

        UatRecorder::record('hotel', '/hotel/pricing', ['hid' => '100000137195'], '{"options":[]}');

        $this->assertSame([100000137195, 100000137200], json_decode(File::get("$dir/TJ test Hotel Search Request.json"), true)['hids']);
        $this->assertSame('100000137195', json_decode(File::get("$dir/TJ test Hotel Search Response.json"), true)['hotels'][0]['hotelId']);
        // The kept batches never go into the zip's test-case folders.
        $this->assertDirectoryDoesNotExist("$dir/.search-batches");
    }

    public function test_case_14_collects_every_cancellation_in_one_cleanup_summary(): void
    {
        UatRecorder::start('hotel', 'HOTEL API TEST CASES/Test Case 14');
        $dir = UatRecorder::root().'/HOTEL API TEST CASES/Test Case 14';

        UatRecorder::record('hotel', '/hotel/cancel-booking/TJP1', [], '{"status":{"success":true,"httpStatus":200}}');
        UatRecorder::record('hotel', '/hotel/booking-details', ['bookingId' => 'TJP1'], '{"order":{"status":"CANCELLED"}}');
        UatRecorder::record('hotel', '/hotel/cancel-booking/TJP2', [], '{"status":{"success":true,"httpStatus":200}}');

        $this->assertSame(['cleanup_summary.json'], collect(File::files($dir))->map->getFilename()->all());
        $this->assertSame([
            'cancelled' => ['TJP1', 'TJP2'],
            'count' => 2,
            'details' => [
                ['bookingId' => 'TJP1', 'response' => ['status' => ['success' => true, 'httpStatus' => 200]]],
                ['bookingId' => 'TJP2', 'response' => ['status' => ['success' => true, 'httpStatus' => 200]]],
            ],
        ], json_decode(File::get("$dir/cleanup_summary.json"), true));
    }

    public function test_nothing_is_recorded_in_production(): void
    {
        UatRecorder::start('flight', 'Tripjack API logs/oneway/X');
        config(['services.tripjack.env' => 'production']);
        UatRecorder::record('flight', '/fms/v1/review', [], '{}');

        $this->assertFileDoesNotExist(UatRecorder::root().'/Tripjack API logs/oneway/X/ReviewRequest.json');
    }
}
