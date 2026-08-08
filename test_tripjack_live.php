<?php
$apiKey       = '4133982984b2ff-40db-4e66-8913-8d64a0a9e001';
$baseUrl      = 'https://apitest-hms.tripjack.com/hms/v3';
$correlationId = uniqid('TYT', true);

// Trying with the city ID that the user saw in their portal URL (cId=699261 for Mumbai)
$payload = json_encode([
    'checkIn'    => '2026-08-14',
    'checkOut'   => '2026-08-16',
    'currency'   => 'INR',
    'nationality'=> '106', // The portal used nationality=106
    'rooms'      => [
        ['adults' => 2, 'children' => []]
    ],
    'cityId'     => '699261', // Mumbai cId from portal
    'countryOfResidence' => '106',
]);

echo "=== Testing /hotel/listing with Mumbai city ID 699261 ===\n";
echo "Payload: $payload\n\n";

$ch = curl_init($baseUrl.'/hotel/listing');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_HEADER         => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'apikey: '.$apiKey,
        'correlationId: '.$correlationId,
    ],
]);
$raw        = curl_exec($ch);
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

$body = substr($raw, $headerSize);
echo "HTTP: $httpCode\n";
echo "RESPONSE:\n$body\n";
