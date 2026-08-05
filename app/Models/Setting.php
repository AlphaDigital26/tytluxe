<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];

    /**
     * Get a setting value by key, with an optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Get a setting value decoded from JSON, with an optional default.
     */
    public static function getJson(string $key, mixed $default = []): mixed
    {
        $value = static::get($key);
        if ($value === null) return $default;
        $decoded = json_decode($value, true);
        return ($decoded !== null) ? $decoded : $default;
    }

    /**
     * Set (upsert) a setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Set a JSON-encoded setting value by key.
     */
    public static function setJson(string $key, mixed $value): void
    {
        static::set($key, json_encode($value));
    }
}
