<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Flight options the client controls from the admin's Flights → Flight
 * Settings page. Every reader in the flight flow goes through here so a
 * missing row (fresh install, settings never saved) falls back to the
 * behaviour the site had before these switches existed.
 */
class FlightSettings
{
    /** ₹ markup per passenger, per flight (founder's decision, 2026-11-02). */
    public const DEFAULT_DOMESTIC_MARKUP = 250.0;

    public const DEFAULT_INTERNATIONAL_MARKUP = 700.0;

    /** ₹ markup per infant, per flight, domestic or international (founder's decision, 2026-11-04). */
    public const DEFAULT_INFANT_MARKUP = 100.0;

    public const DEFAULT_DISABLED_MESSAGE = 'Online flight booking is paused for a short while. Please call or WhatsApp us and our team will book your flight for you.';

    /**
     * Values read during this request/job only — the class is bound as a
     * scoped instance (AppServiceProvider), which Laravel drops between
     * requests and between queued jobs, so a long-running worker never keeps
     * a stale markup after the client changes it.
     *
     * @var array<string, mixed>
     */
    protected array $values = [];

    public static function bookingEnabled(): bool
    {
        return self::bool('flights.booking_enabled', true);
    }

    public static function disabledMessage(): string
    {
        return (string) (self::get('flights.disabled_message') ?: self::DEFAULT_DISABLED_MESSAGE);
    }

    /** ₹ markup per passenger on each domestic flight. */
    public static function domesticMarkup(): float
    {
        return self::amount('flights.domestic_markup', self::DEFAULT_DOMESTIC_MARKUP);
    }

    /** ₹ markup per passenger on each international flight. */
    public static function internationalMarkup(): float
    {
        return self::amount('flights.international_markup', self::DEFAULT_INTERNATIONAL_MARKUP);
    }

    public static function allowGuestCancel(): bool
    {
        return self::bool('flights.allow_guest_cancel', true);
    }

    public static function allowGuestReschedule(): bool
    {
        return self::bool('flights.allow_guest_reschedule', true);
    }

    public static function allowGuestExtras(): bool
    {
        return self::bool('flights.allow_guest_extras', true);
    }

    public static function lowBalanceAlert(): float
    {
        $value = self::get('flights.low_balance_alert');

        return is_numeric($value) ? (float) $value : (float) config('services.tripjack.flight.low_balance_alert');
    }

    /** Shown to guests wherever a switched-off self-service option would have been. */
    public static function contactUsMessage(): string
    {
        return 'Please contact our support team and we will take care of this for you.';
    }

    /**
     * @param  array<string, mixed>  $values  key (without the "flights." prefix) => value
     */
    public static function save(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::set('flights.'.$key, is_bool($value) ? ($value ? '1' : '0') : (string) $value);
        }
        app()->forgetInstance(self::class);
    }

    /** ₹ markup per infant (under 2) on each flight, domestic or international. */
    public static function infantMarkup(): float
    {
        return self::amount('flights.infant_markup', self::DEFAULT_INFANT_MARKUP);
    }

    protected static function amount(string $key, float $default): float
    {
        $value = self::get($key);

        return is_numeric($value) ? max(0.0, (float) $value) : $default;
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
