<?php

namespace App\Services\TripJack;

/**
 * Maps TripJack's numeric error codes (6502-6540, per their v3 error
 * reference) to a customer-facing message, a log severity, and whether the
 * failure is worth retrying / re-searching. Without this, every failure —
 * from "price changed" to "our wallet is out of funds" — collapsed into the
 * same generic "please try again" message, which is both unhelpful to the
 * customer and hides operationally-critical errors (insufficient balance,
 * suspended key) in routine warning-level logs.
 */
class TripJackErrorCatalog
{
    /**
     * @return array{message: string, logLevel: string, action: string}
     *   action is one of: retry, re_search, contact_support, none
     */
    public static function describe(?string $code, string $fallbackMessage = ''): array
    {
        return match ($code) {
            '6502' => [ // SEARCH_SESSION_EXPIRED
                'message' => 'Your search has expired. Please search again to see current rates.',
                'logLevel' => 'info',
                'action' => 're_search',
            ],
            '6503', '6504' => [ // HOTEL_ID_MISMATCH_WITH_SESSION / INVALID_HOTEL_ID
                'message' => 'This hotel is no longer available. Please search again.',
                'logLevel' => 'warning',
                'action' => 're_search',
            ],
            '6505' => [ // OPTION_ID_NOT_FOUND
                'message' => 'This room option has expired. Please choose a room again.',
                'logLevel' => 'info',
                'action' => 're_search',
            ],
            '6506' => [ // OPTION_SOLD_OUT
                'message' => 'This room just sold out. Please pick another option.',
                'logLevel' => 'info',
                'action' => 're_search',
            ],
            '6507', '6508' => [ // GUEST_INFO_INCOMPLETE / GUEST_NAME_INVALID
                'message' => 'Please check the guest details you entered — a name looks incomplete or invalid.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            '6509' => [ // TRAVELLER_COUNT_MISMATCH
                'message' => 'The number of guests no longer matches this room. Please select your room again.',
                'logLevel' => 'error', // shouldn't happen if we derive counts from the reviewed option
                'action' => 're_search',
            ],
            '6510' => [ // DUPLICATE_BOOKING_REQUEST
                'message' => 'This booking is already being processed. Please check your bookings before trying again.',
                'logLevel' => 'warning',
                'action' => 'contact_support',
            ],
            '6528', '6529', '6530', '6531' => [ // CHILD_AGE_*
                'message' => 'Please provide a valid age for every child before searching.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            '6532' => [ // ADULTS_PER_ROOM_EXCEEDS_MAXIMUM
                'message' => 'Too many adults in one room. Please split your group across more rooms.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            '6533' => [ // CURRENCY_NOT_SUPPORTED
                'message' => 'This currency isn\'t supported for this search.',
                'logLevel' => 'error', // we always send INR — this means a bug on our side
                'action' => 'contact_support',
            ],
            '6534' => [ // NATIONALITY_RESTRICTED
                'message' => 'This hotel can\'t be booked for the selected guest nationality due to a property or country restriction.',
                'logLevel' => 'info',
                'action' => 'none',
            ],
            '6535' => [ // PRICE_CHANGED
                'message' => 'The price for this room has changed. Please review the new price before booking.',
                'logLevel' => 'info',
                'action' => 're_search',
            ],
            '6536' => [ // ORDER_INVALID_ACTION
                'message' => 'This action isn\'t available for this booking right now.',
                'logLevel' => 'warning',
                'action' => 'contact_support',
            ],
            '6537' => [ // HOLD_NOT_ALLOWED
                'message' => 'This rate requires payment at the time of booking and can\'t be held — online payment isn\'t available yet. Please choose a different room, or contact us to book this one directly.',
                'logLevel' => 'info',
                'action' => 're_search',
            ],
            '6538' => [ // DUPLICATE_ORDER_CREDIT_LINE_OR_WALLET
                'message' => 'A booking for this stay already exists. Please check your bookings before trying again.',
                'logLevel' => 'warning',
                'action' => 'contact_support',
            ],
            '6539' => [ // INSUFFICIENT_BALANCE — operationally critical, never expose the real reason to the customer
                'message' => 'We couldn\'t complete this booking right now. Our team has been notified and will follow up shortly.',
                'logLevel' => 'critical',
                'action' => 'contact_support',
            ],
            '6540' => [ // HOLD_PAYMENT_NOT_ALLOWED
                'message' => 'This booking could not be processed. Please try again.',
                'logLevel' => 'error', // we never send paymentInfos on a hold — this means a bug on our side
                'action' => 'contact_support',
            ],
            '6514', '6515', '6516' => [ // SUPPLIER_UNAVAILABLE / SUPPLIER_TIMEOUT / INTERNAL_ERROR
                'message' => 'The hotel supplier is temporarily unavailable. Please try again in a moment.',
                'logLevel' => 'warning',
                'action' => 'retry',
            ],
            '6517' => [ // RATE_LIMITED
                'message' => 'We\'re receiving a lot of requests right now. Please try again shortly.',
                'logLevel' => 'warning',
                'action' => 'retry',
            ],
            '6518', '6519', '6520' => [ // UNAUTHORIZED / FORBIDDEN / API_KEY_SUSPENDED — account-level, critical
                'message' => 'We couldn\'t complete this booking right now. Our team has been notified and will follow up shortly.',
                'logLevel' => 'critical',
                'action' => 'contact_support',
            ],
            '6521' => [ // INVALID_REQUEST_FORMAT — our bug
                'message' => 'Something went wrong on our end. Please try again.',
                'logLevel' => 'error',
                'action' => 'retry',
            ],
            '6522', '6523', '6524', '6525', '6526' => [ // date-range validation
                'message' => 'Please check your check-in and check-out dates and try again.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            '6527' => [ // INVALID_ROOM_CONFIG
                'message' => 'Please check your room and guest selection and try again.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            default => [
                'message' => $fallbackMessage !== '' ? $fallbackMessage : 'Something went wrong. Please try again.',
                'logLevel' => 'warning',
                'action' => 'retry',
            ],
        };
    }

    /**
     * Extracts the numeric errCode from either error envelope shape TripJack
     * uses: {"errors":[{"errCode":"..."}]} (business failures) or
     * {"error":{"code":"..."}} (the documented standardised shape).
     */
    public static function codeFromResponse(array $response): ?string
    {
        return $response['errors'][0]['errCode']
            ?? $response['error']['code']
            ?? null;
    }
}
