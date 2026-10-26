<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A real GSTIN: 2-digit state code, the holder's PAN, entity number, "Z",
 * and a check character computed from the first 14 (GSTN's mod-36 scheme).
 * TripJack's flight Book rejects anything else (error 805) — after the
 * guest has paid — so it is checked here first.
 */
class Gstin implements ValidationRule
{
    private const CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public static function isValid(string $gstin): bool
    {
        $gstin = strtoupper(trim($gstin));
        if (! preg_match('/^(0[1-9]|[1-3][0-9])[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/', $gstin)) {
            return false;
        }

        return self::checkCharacter(substr($gstin, 0, 14)) === $gstin[14];
    }

    public static function checkCharacter(string $first14): string
    {
        $sum = 0;
        foreach (str_split(strtoupper($first14)) as $i => $char) {
            $product = strpos(self::CHARS, $char) * ($i % 2 === 0 ? 1 : 2);
            $sum += intdiv($product, 36) + $product % 36;
        }

        return self::CHARS[(36 - $sum % 36) % 36];
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! self::isValid($value)) {
            $fail('Please enter a valid 15-character GSTIN, e.g. 27AAPFU0939F1ZV.');
        }
    }
}
