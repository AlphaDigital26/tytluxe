<?php

namespace App\Services\TripJack;

/**
 * Maps TripJack Flights API v2.0's numeric error codes to a customer-facing
 * message, a log severity, and whether the failure is worth retrying /
 * re-searching. Structurally identical to TripJackErrorCatalog (hotels) —
 * kept as a separate class since the two APIs' codes overlap numerically
 * (e.g. flight's 400 vs hotel's 6502) but mean completely different things.
 */
class TripJackFlightErrorCatalog
{
    /**
     * @return array{message: string, logLevel: string, action: string}
     *   action is one of: retry, re_search, contact_support, none
     */
    public static function describe(?string $code, string $fallbackMessage = ''): array
    {
        return match ($code) {
            // Confirmed live (Air India, 2026-10-01): "No matching reissue
            // configuration found for the given supplier" — this airline
            // can't be rescheduled through the API at all, so retrying won't
            // help.
            '1157' => [
                'message' => 'Online rescheduling isn\'t available for this airline. Please contact our support team and we\'ll reschedule it for you.',
                'logLevel' => 'info',
                'action' => 'contact_support',
            ],
            '400' => [
                'message' => 'Something went wrong on our end. Please try again.',
                'logLevel' => 'error',
                'action' => 'retry',
            ],
            '404' => [
                'message' => 'We couldn\'t find that. Please try again.',
                'logLevel' => 'warning',
                'action' => 'retry',
            ],
            '407' => [
                'message' => 'That search isn\'t supported. Please adjust your search and try again.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            '408', '411' => [ // Access Denied / Insufficient permission
                'message' => 'We couldn\'t complete this request right now. Our team has been notified.',
                'logLevel' => 'critical',
                'action' => 'contact_support',
            ],
            '412' => [ // Invalid API key
                'message' => 'We couldn\'t complete this request right now. Our team has been notified.',
                'logLevel' => 'critical',
                'action' => 'contact_support',
            ],
            '802' => [ // Account inactive
                'message' => 'We couldn\'t complete this booking right now. Our team has been notified and will follow up shortly.',
                'logLevel' => 'critical',
                'action' => 'contact_support',
            ],
            '805', '806', '819' => [ // Invalid GSTIN / Invalid Email or Mobile / Invalid Address
                'message' => 'Please check the details you entered and try again.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            '810' => [
                'message' => 'Something went wrong on our end. Please try again.',
                'logLevel' => 'error',
                'action' => 'retry',
            ],
            '816' => [ // Duplicate request
                'message' => 'This booking is already being processed. Please check your bookings before trying again.',
                'logLevel' => 'warning',
                'action' => 'contact_support',
            ],
            '1000' => [ // Flight no longer available
                'message' => 'This flight just sold out. Please search again.',
                'logLevel' => 'info',
                'action' => 're_search',
            ],
            '1001', '1002', '1006' => [ // pax count validation
                'message' => 'Please check your passenger counts and try again.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            '1003', '1005' => [ // date/route validation
                'message' => 'Please check your travel dates and route and try again.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            '1009' => [
                'message' => 'Your selection has expired. Please search again.',
                'logLevel' => 'info',
                'action' => 're_search',
            ],
            '1010' => [ // Duplicate passenger names
                'message' => 'Each traveller must have a unique name. Please check the details you entered.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            '1012', '1013', '1014', '1051', '1052', '1053' => [ // age / DOB validation
                'message' => 'Please check the traveller ages/date of birth you entered.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            '1015' => [ // Payment amount mismatch
                'message' => 'The fare has changed since you reviewed it. Please try again.',
                'logLevel' => 'error', // shouldn't happen if we always book off a fresh Review
                'action' => 're_search',
            ],
            '1056' => [
                'message' => 'Seat selection isn\'t available for this flight.',
                'logLevel' => 'info',
                'action' => 'none',
            ],
            '1057' => [ // Booking not found
                'message' => 'We couldn\'t find this booking with the airline. Our team has been notified.',
                'logLevel' => 'error',
                'action' => 'contact_support',
            ],
            '1059' => [ // Hold time limit expired
                'message' => 'This booking hold has expired. Please search and book again.',
                'logLevel' => 'info',
                'action' => 're_search',
            ],
            '1064', '1065', '1066', '1067' => [ // passport validation
                'message' => 'Please check the passport details you entered — the number, issue date, or expiry date looks invalid, or the passport expires too soon for this trip.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            '1071' => [ // Fare no longer available
                'message' => 'This fare is no longer available. Please search again.',
                'logLevel' => 'info',
                'action' => 're_search',
            ],
            '2540' => [
                'message' => 'We couldn\'t calculate cancellation charges right now. Our team has been notified.',
                'logLevel' => 'error',
                'action' => 'contact_support',
            ],
            '2541', '2544' => [ // amendment already raised
                'message' => 'A request for this booking is already being processed.',
                'logLevel' => 'info',
                'action' => 'none',
            ],
            '2542', '2545', '2546', '2549', '2550', '2551', '2552', '2556', '2560', '2567', '2568' => [
                'message' => 'Something went wrong with this request. Please try again or contact us.',
                'logLevel' => 'error', // most of these mean a bug in the request we built
                'action' => 'contact_support',
            ],
            '2543' => [ // Travel date has passed
                'message' => 'This trip has already departed, so it can\'t be cancelled online.',
                'logLevel' => 'info',
                'action' => 'contact_support',
            ],
            '2553' => [
                'message' => 'We couldn\'t process this request. Our team has been notified.',
                'logLevel' => 'error',
                'action' => 'contact_support',
            ],
            '2569' => [ // Senior citizen age requirement
                'message' => 'This fare requires the passenger to be a senior citizen at the time of travel.',
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
     * Extracts the numeric error code from TripJack's flight error envelope.
     * Mirrors TripJackErrorCatalog::codeFromResponse() — same shapes.
     */
    public static function codeFromResponse(array $response): ?string
    {
        return $response['errors'][0]['errCode']
            ?? $response['error']['code']
            ?? null;
    }
}
