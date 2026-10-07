<?php

namespace App\Support;

/**
 * Display name for a TripJack option's rooms. Same room type in every room →
 * just that name; a mixed-room option (CRSM/CRCM) → "Room 1: Deluxe |
 * Room 2: Standard", as TripJack recommends, so the guest can see which
 * room gets which type (a joined "Deluxe + Standard" hid that).
 */
class RoomLabel
{
    public static function forOption(array $option, string $fallback = ''): string
    {
        $names = collect($option['roomInfo'] ?? [])->pluck('name')->map(fn ($n) => trim((string) $n))->filter()->values();

        if ($names->isEmpty()) {
            return $fallback;
        }
        if ($names->unique()->count() === 1) {
            return $names->first();
        }

        return $names->map(fn ($name, $i) => 'Room '.($i + 1).': '.$name)->implode(' | ');
    }
}
