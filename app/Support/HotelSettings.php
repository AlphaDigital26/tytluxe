<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Hotel options the client controls from the admin's Hotels → Hotel
 * Settings page. A missing row (settings never saved) falls back to how the
 * site behaved before these switches existed. Bound as a scoped instance
 * (AppServiceProvider) so values are re-read for every request and job —
 * same approach as FlightSettings.
 */
class HotelSettings
{
    public const DEFAULT_MARKUP_PERCENT = 10.0;

    public const DEFAULT_DISABLED_MESSAGE = 'Online hotel booking is paused for a short while. Please call or WhatsApp us and our team will book your stay for you.';

    /** @var array<string, mixed> */
    protected array $values = [];

    public static function bookingEnabled(): bool
    {
        return self::bool('hotels.booking_enabled', true);
    }

    public static function disabledMessage(): string
    {
        return (string) (self::get('hotels.disabled_message') ?: self::DEFAULT_DISABLED_MESSAGE);
    }

    public static function markupPercent(): float
    {
        $value = self::get('hotels.markup_percent');

        return is_numeric($value) ? max(0.0, min(100.0, (float) $value)) : self::DEFAULT_MARKUP_PERCENT;
    }

    /** Markup as a fraction (10% → 0.10), the form HotelPricingService uses. */
    public static function marginRate(): float
    {
        return self::markupPercent() / 100;
    }

    public static function allowGuestCancel(): bool
    {
        return self::bool('hotels.allow_guest_cancel', true);
    }

    public static function contactUsMessage(): string
    {
        return 'To cancel or change this booking, please contact our support team and we will take care of it for you.';
    }

    /**
     * @param  array<string, mixed>  $values  key (without the "hotels." prefix) => value
     */
    public static function save(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::set('hotels.'.$key, is_bool($value) ? ($value ? '1' : '0') : (string) $value);
        }
        app()->forgetInstance(self::class);
    }

    protected static function bool(string $key, bool $default): bool
    {
        $value = self::get($key);

        return $value === null ? $default : $value === '1';
    }

    protected static function get(string $key): mixed
    {
        $store = app(self::class);
        if (! array_key_exists($key, $store->values)) {
            $store->values[$key] = Setting::get($key);
        }

        return $store->values[$key];
    }
}
