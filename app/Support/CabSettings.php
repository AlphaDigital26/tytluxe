<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Cab options the client controls from the admin's Cabs → Cab Settings page.
 * A missing row (settings never saved) falls back to the defaults here.
 */
class CabSettings
{
    /** ₹ markup per cab booking (one way; a round trip pays it twice). Set by the client in Cab Settings. */
    public const DEFAULT_MARKUP = 0.0;

    public const DEFAULT_DISABLED_MESSAGE = 'Online cab booking is paused for a short while. Please call or WhatsApp us and our team will book your cab for you.';

    /** @var array<string, mixed> values read during this request (bound as a scoped instance) */
    protected array $values = [];

    public static function bookingEnabled(): bool
    {
        return self::bool('cabs.booking_enabled', true);
    }

    public static function disabledMessage(): string
    {
        return (string) (self::get('cabs.disabled_message') ?: self::DEFAULT_DISABLED_MESSAGE);
    }

    /** ₹ markup on each one-way cab trip. */
    public static function markup(): float
    {
        $value = self::get('cabs.markup');

        return is_numeric($value) ? max(0.0, (float) $value) : self::DEFAULT_MARKUP;
    }

    public static function allowGuestCancel(): bool
    {
        return self::bool('cabs.allow_guest_cancel', true);
    }

    public static function contactUsMessage(): string
    {
        return 'Please contact our support team and we will take care of this for you.';
    }

    /** @param  array<string, mixed>  $values  key (without the "cabs." prefix) => value */
    public static function save(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::set('cabs.'.$key, is_bool($value) ? ($value ? '1' : '0') : (string) $value);
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
