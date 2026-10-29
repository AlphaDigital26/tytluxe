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

            return;
        }

        // A slip in a big provider's name ("gmial.com", "gmail.con") would
        // send the confirmation and payment reminders nowhere. Suggest the fix.
        if ($suggestion = self::typoFix($domain)) {
            $fail('Please check your email address. Did you mean '.strstr($value, '@', true).'@'.$suggestion.'?');
        }
    }

    /** Providers most guests use, and real domains that merely look like a typo of one. */
    protected const PROVIDERS = ['gmail.com', 'yahoo.com', 'yahoo.co.in', 'hotmail.com', 'outlook.com', 'rediffmail.com', 'icloud.com', 'live.com'];

    protected const LOOKALIKES_THAT_ARE_REAL = ['mail.com', 'ymail.com', 'gmx.com', 'email.com', 'yahoo.in', 'yahoo.co.uk', 'outlook.in', 'live.in', 'me.com', 'mac.com'];

    protected const TLD_TYPOS = ['con' => 'com', 'cmo' => 'com', 'ocm' => 'com', 'comm' => 'com', 'coom' => 'com', 'vom' => 'com', 'xom' => 'com'];

    /** The corrected domain for a likely typo, or null when the domain looks fine. */
    public static function typoFix(string $domain): ?string
    {
        $domain = strtolower($domain);
        if (in_array($domain, self::PROVIDERS, true) || in_array($domain, self::LOOKALIKES_THAT_ARE_REAL, true)) {
            return null;
        }

        // Fix a mistyped ending first ("gmail.con" → "gmail.com").
        $fixed = preg_replace_callback('/\.([a-z]+)$/', fn ($m) => '.'.(self::TLD_TYPOS[$m[1]] ?? $m[1]), $domain);
        if (in_array($fixed, self::PROVIDERS, true)) {
            return $fixed;
        }

        // Then one or two letters off a provider's name ("gmial.com", "yahooo.com").
        foreach (self::PROVIDERS as $provider) {
            $distance = levenshtein($fixed, $provider);
            if ($distance >= 1 && $distance <= 2 && strlen($fixed) >= 6) {
                return $provider;
            }
        }

        return $fixed !== $domain ? $fixed : null;
    }
}
