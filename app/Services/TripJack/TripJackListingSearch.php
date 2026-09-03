<?php

namespace App\Services\TripJack;

use App\Models\Destination;
use App\Models\Hotel;
use Illuminate\Support\Collection;

class TripJackListingSearch
{
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
            ->limit(100) // TripJack hard cap per listing request
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

        $hidsToPrice = collect($hids)->take(100)->values()->all();

        $response = $this->client->listing($checkIn, $checkOut, $rooms, $hidsToPrice, $correlationId, $currency, $nationality);

        $options = collect($response['hotels'] ?? [])
            ->mapWithKeys(function ($hotel) use ($currency) {
                $tjHotelId = (string) ($hotel['hotelId'] ?? $hotel['tjHotelId'] ?? '');
                if ($tjHotelId === '' || empty($hotel['options'])) {
                    return [];
                }

                $cheapest = collect($hotel['options'])->sortBy('pricing.totalPrice')->first();

                return [$tjHotelId => [
                    'optionId' => $cheapest['optionId'] ?? null,
                    'totalPrice' => $cheapest['pricing']['totalPrice'] ?? null,
                    'currency' => $cheapest['pricing']['currency'] ?? $currency,
                    'mealBasis' => $cheapest['mealBasis'] ?? null,
                    'isRefundable' => $cheapest['cancellation']['isRefundable'] ?? null,
                ]];
            });

        return ['correlationId' => $correlationId, 'options' => $options];
    }
}
