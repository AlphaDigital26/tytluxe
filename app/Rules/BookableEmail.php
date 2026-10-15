<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

/**
 * An email a supplier will accept for a booking's contact. Checked before
 * payment: TripJack refuses addresses on test-only domains (e.g. ".test",
 * found in the flight test run), which used to fail Book after the guest
 * had paid and end in a refund.
 */
class BookableEmail implements ValidationRule
{
    /** Domains reserved for testing/docs (RFC 2606, 6761) — never real inboxes. */
    protected const RESERVED_TLDS = ['test', 'example', 'invalid', 'localhost', 'local'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;
        $message = 'Please enter a valid email address, like name@gmail.com — we send your booking confirmation there.';

        if (Validator::make(['e' => $value], ['e' => 'email:rfc,strict'])->fails()) {
            $fail($message);

            return;
        }

        $domain = strtolower(substr(strrchr($value, '@'), 1));
        $tld = str_contains($domain, '.') ? substr(strrchr($domain, '.'), 1) : '';

        if ($tld === '' || strlen($tld) < 2 || in_array($tld, self::RESERVED_TLDS, true) || str_starts_with($domain, 'example.')) {
            $fail($message);
        }
    }
}
