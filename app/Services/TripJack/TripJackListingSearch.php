<?php

namespace App\Services\TripJack;

use App\Models\Destination;
use App\Models\Hotel;
use App\Services\HotelPricingService;
use Illuminate\Support\Collection;

class TripJackListingSearch
{
    /** TripJack's hard cap on hotel IDs per listing request. */
    public const MAX_HIDS = 100;

    public function __construct(protected TripJackClient $client)
    {
    }

    /**
     * Live-price the TripJack hotels already synced locally for a destination.
     *
     * @param  array<int, array{adults:int, children?:int, childAge?:int[]}>  $rooms
     * @return array{correlationId: string, options: Collection<string, array>}
     */
    public function search(Destination $destination, string $checkIn, string $checkOut, array $rooms, string $currency = 'INR', string $nationality = '106'): array
    {
        $hids = Hotel::where('destination_id', $destination->id)
            ->where('source', 'tripjack')
            ->where('is_active', true)
            ->whereNotNull('tripjack_hotel_id')
            ->orderBy('id') // deterministic — otherwise which 100/189 hotels get priced is undefined
            ->limit(self::MAX_HIDS)
            ->pluck('tripjack_hotel_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return $this->searchByHids($hids, $checkIn, $checkOut, $rooms, $currency, $nationality);
    }

    /**
     * Live-price a specific list of TripJack hotel IDs.
     *
     * @param  array<int>  $hids
     * @param  array<int, array{adults:int, children?:int, childAge?:int[]}>  $rooms
     * @return array{correlationId: string, options: Collection<string, array>}
     */
    public function searchByHids(array $hids, string $checkIn, string $checkOut, array $rooms, string $currency = 'INR', string $nationality = '106'): array
    {
        $correlationId = TripJackClient::newCorrelationId();

        if (empty($hids)) {
            return ['correlationId' => $correlationId, 'options' => collect()];
        }

        $hidsToPrice = collect($hids)->take(self::MAX_HIDS)->values()->all();

        $response = $this->client->listing($checkIn, $checkOut, $rooms, $hidsToPrice, $correlationId, $currency, $nationality);

        return ['correlationId' => $correlationId, 'options' => $this->mapOptions($response['hotels'] ?? [], $currency)];
    }

    /**
     * Live-price every hid for a whole city, split into TripJack-sized batches
     * sent concurrently under one correlationId (the same id must carry through
     * to Detail/Review/Book for whichever hotel the guest picks).
     *
     * @param  array<int>  $hids
     * @param  array<int, array{adults:int, children?:int, childAge?:int[]}>  $rooms
     * @return array{correlationId: string, options: Collection<string, array>, batches: int, failedBatches: int, firstError: ?array}
     */
    public function searchCity(array $hids, string $checkIn, string $checkOut, array $rooms, string $currency = 'INR', string $nationality = '106'): array
    {
        $correlationId = TripJackClient::newCorrelationId();
        $batches = array_chunk(array_values(array_unique($hids)), self::MAX_HIDS);

        if (empty($batches)) {
            return ['correlationId' => $correlationId, 'options' => collect(), 'batches' => 0, 'failedBatches' => 0, 'firstError' => null];
        }

        $responses = $this->client->listingBatches($batches, $checkIn, $checkOut, $rooms, $correlationId, $currency, $nationality);

        $options = collect();
        $failed = 0;
        foreach ($responses as $response) {
            if ($response === null) {
                $failed++;

                continue;
            }
            $options = $options->union($this->mapOptions($response['hotels'] ?? [], $currency));
        }

        return ['correlationId' => $correlationId, 'options' => $options, 'batches' => count($batches), 'failedBatches' => $failed, 'firstError' => $this->client->lastBatchError];
    }

    /**
     * Reduces each TripJack listing hotel to its cheapest option, keyed by hid.
     *
     * @return Collection<string, array>
     */
    private function mapOptions(array $hotels, string $currency): Collection
    {
        return collect($hotels)
            ->mapWithKeys(function ($hotel) use ($currency) {
                $tjHotelId = (string) ($hotel['hotelId'] ?? $hotel['tjHotelId'] ?? '');
                if ($tjHotelId === '' || empty($hotel['options'])) {
                    return [];
                }

                $cheapest = collect($hotel['options'])->sortBy('pricing.totalPrice')->first();
                $tripjackTotalPrice = $cheapest['pricing']['totalPrice'] ?? null;
                $pricing = $tripjackTotalPrice !== null ? HotelPricingService::price((float) $tripjackTotalPrice) : null;

                return [$tjHotelId => [
                    'optionId' => $cheapest['optionId'] ?? null,
                    // Raw TripJack price — internal/audit use only, never display this to the customer.
                    'totalPrice' => $tripjackTotalPrice,
                    // The actual customer-facing price — display this everywhere.
                    'customerPrice' => $pricing['customer_price'] ?? null,
                    'pricingBreakdown' => $pricing,
                    'currency' => $cheapest['pricing']['currency'] ?? $currency,
                    'mealBasis' => $cheapest['mealBasis'] ?? null,
                    'isRefundable' => $cheapest['cancellation']['isRefundable'] ?? null,
                    'cancellation' => $cheapest['cancellation'] ?? null,
                ]];
            });
    }
}
