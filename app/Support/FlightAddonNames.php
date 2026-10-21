<?php

namespace App\Support;

/**
 * Plain names for airline add-on codes ("SFML" → "Seafood meal") on the
 * admin flight page. Names TripJack gave at booking time (saved in
 * flight_segments_payload.ssrNames) win; older bookings fall back to the
 * standard IATA special-meal codes. Airline-specific codes (IndiGo's
 * "HCCB", …) without a saved name are shown as the code.
 */
class FlightAddonNames
{
    public const IATA_MEALS = [
        'AVML' => 'Asian vegetarian meal',
        'BBML' => 'Baby meal',
        'BLML' => 'Bland meal',
        'CHML' => 'Child meal',
        'DBML' => 'Diabetic meal',
        'FPML' => 'Fruit platter',
        'GFML' => 'Gluten-free meal',
        'HNML' => 'Hindu meal (non-vegetarian)',
        'IVML' => 'Indian vegetarian meal',
        'JNML' => 'Jain meal',
        'KSML' => 'Kosher meal',
        'LCML' => 'Low-calorie meal',
        'LFML' => 'Low-fat meal',
        'LSML' => 'Low-salt meal',
        'MOML' => 'Muslim meal',
        'NLML' => 'Lactose-free meal',
        'RVML' => 'Raw vegetarian meal',
        'SFML' => 'Seafood meal',
        'SPML' => 'Special meal',
        'VGML' => 'Vegan meal',
        'VJML' => 'Jain vegetarian meal',
        'VLML' => 'Vegetarian meal (with milk and eggs)',
        'VOML' => 'Oriental vegetarian meal',
    ];

    /**
     * Every add-on code TripJack offered in a Review response, with its
     * description — saved on the booking so the admin can read it later.
     *
     * @return array<string, string>
     */
    public static function fromReview(array $response): array
    {
        $names = [];
        foreach ($response['tripInfos'] ?? [] as $trip) {
            foreach ($trip['sI'] ?? [] as $seg) {
                foreach ($seg['ssrInfo'] ?? [] as $items) {
                    foreach ((array) $items as $item) {
                        if (! empty($item['code']) && ! empty($item['desc']) && $item['desc'] !== $item['code']) {
                            $names[$item['code']] = (string) $item['desc'];
                        }
                    }
                }
            }
        }

        return $names;
    }

    /** @param  array<string, string>  $saved */
    public static function name(string $code, array $saved = []): string
    {
        $name = $saved[$code] ?? self::IATA_MEALS[strtoupper($code)] ?? null;

        return $name ? $name.' ('.$code.')' : $code;
    }
}
