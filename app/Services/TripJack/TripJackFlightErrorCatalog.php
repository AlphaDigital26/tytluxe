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
            // Each told plainly (confirmed in scenario testing, 24 Oct 2026).
            '1003' => [ // "Travel date should be in ascending order"
                'message' => 'Each flight must be on or after the date of the one before it. Please check your dates.',
                'logLevel' => 'info',
                'action' => 'none',
            ],
            '1004' => [ // "Travel date can't be after year"
                'message' => 'Airlines open bookings up to about a year ahead. Please choose an earlier date.',
                'logLevel' => 'info',
                'action' => 'none',
            ],
            '1005' => [ // "Origin and Destination can't be same"
                'message' => 'The departure and arrival cities are the same. Please choose a different destination.',
                'logLevel' => 'info',
                'action' => 'none',
            ],
            '1045' => [ // "Invalid airport"
                'message' => 'We couldn’t find one of those airports. Please choose the city or airport from the list.',
                'logLevel' => 'info',
                'action' => 'none',
            ],
            '2502' => [ // "Duplicate Booking. This is a duplicate booking of TJS…" (seen in testing)
                'message' => 'These travellers are already booked on this flight, so we didn’t book it twice. Please check My Trips — any amount paid for this attempt is refunded automatically.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            // Amendments (cancel / reschedule), from TripJack's "Post-Booking
            // Amendment Errors" reference — each told in the guest's terms.
            '1152', '8037', '8042', '1162' => [ // too close to departure / no-show window
                'message' => 'The airline doesn’t allow online changes this close to departure. Please call or WhatsApp our team and we’ll help.',
                'logLevel' => 'info',
                'action' => 'contact_support',
            ],
            '2603' => [ // journey checked in
                'message' => 'You’ve already checked in for this flight, so it can’t be changed online. Please contact the airline or our team.',
                'logLevel' => 'info',
                'action' => 'contact_support',
            ],
            '2512', '2518' => [ // previous amendment in progress / order pending
                'message' => 'An earlier change on this booking is still being processed. Please try again once it completes.',
                'logLevel' => 'info',
                'action' => 'none',
            ],
            '2600', '1156', '1165', '1164' => [ // already reissued / reissue limit reached
                'message' => 'This ticket has already been changed, so further changes are handled by our team. Please call or WhatsApp us.',
                'logLevel' => 'info',
                'action' => 'contact_support',
            ],
            '1078' => [ // infant's adult must be included
                'message' => 'An infant travels on an adult’s lap, so please include that adult when cancelling or changing the infant.',
                'logLevel' => 'info',
                'action' => 'none',
            ],
            '1079', '1084', '1082' => [ // children/infants can't be left without an adult
                'message' => 'Children and infants can’t be left on the booking without an adult. Please include them as well.',
                'logLevel' => 'info',
                'action' => 'none',
            ],
            '2506', '1159', '1147', '2607' => [ // partial change not allowed for this fare
                'message' => 'This fare can only be changed for the whole booking, not for some travellers or flights. Please choose the full booking.',
                'logLevel' => 'info',
                'action' => 'none',
            ],
            '2621', '2612', '2527', '2644' => [ // void not eligible
                'message' => 'Same-day free cancellation isn’t available for this booking. Please use the normal cancellation instead.',
                'logLevel' => 'info',
                'action' => 'none',
            ],
            '1171' => [ // reissue blocked: booking has SSR
                'message' => 'This booking has added seats, meals or baggage, so the airline doesn’t allow an online date change. Please call or WhatsApp our team.',
                'logLevel' => 'info',
                'action' => 'contact_support',
            ],
            '1153', '1168', '2599', '2583', '2585', '1167', '2646' => [ // reissue / amendment not allowed for this booking type
                'message' => 'This booking can’t be changed online. Please call or WhatsApp our team and we’ll arrange it with the airline.',
                'logLevel' => 'info',
                'action' => 'contact_support',
            ],
            '1154', '1155', '2548' => [ // dates invalid for reissue
                'message' => 'Please choose a new travel date in the future, before your original flight has departed.',
                'logLevel' => 'info',
                'action' => 'none',
            ],
            '8113', '1148' => [ // no reissue flights for the date
                'message' => 'No flights are available on the new date you chose. Please try another date.',
                'logLevel' => 'info',
                'action' => 'none',
            ],
            '2503' => [ // review expired
                'message' => 'The new fare has expired. Please search for the new date again.',
                'logLevel' => 'info',
                'action' => 're_search',
            ],
            '2563' => [ // unable to raise amendment
                'message' => 'The airline couldn’t process this change right now. Please try again in a little while, or contact our team.',
                'logLevel' => 'warning',
                'action' => 'retry',
            ],
            '808' => [ // "Keys Passed in the request is already expired"
                'message' => 'These fares have expired. Please search again to see current prices.',
                'logLevel' => 'info',
                'action' => 're_search',
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
            '2560' => [ // Emergency contact fields cannot be null
                'message' => 'Please fill in the emergency contact details for this booking.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            '2567', '2568' => [ // Document ID not provided / has special characters
                'message' => 'Please enter a valid ID number (letters and numbers only) for each traveller on this fare.',
                'logLevel' => 'warning',
                'action' => 'none',
            ],
            '2542', '2545', '2546', '2549', '2550', '2551', '2552', '2556' => [
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
     * Codes that mean a traveller/contact detail the guest typed was
     * rejected (doc error table: GSTIN, email/mobile, address, pax counts,
     * duplicate names, ages/DOB, passport, emergency contact, document ID)
     * — the fix is to correct the form, not to search for another flight.
     */
    public static function isTravellerInputError(?string $code): bool
    {
        return in_array($code, [
            '805', '806', '819',
            '1001', '1002', '1006', '1010',
            '1012', '1013', '1014', '1051', '1052', '1053',
            '1064', '1065', '1066', '1067',
            '2560', '2567', '2568', '2569',
        ], true);
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
