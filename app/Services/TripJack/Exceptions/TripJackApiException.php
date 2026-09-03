<?php

namespace App\Services\TripJack\Exceptions;

class TripJackApiException extends TripJackException
{
    public function __construct(
        string $message,
        public readonly int $status,
        public readonly ?string $errorCode = null,
        public readonly array $body = [],
    ) {
        parent::__construct($message);
    }
}
