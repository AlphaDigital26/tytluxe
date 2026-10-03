<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class TripJackLowBalanceAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public float $totalBalance,
        public float $threshold,
        public Carbon $detectedAt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('TripJack balance is low')
            ->greeting('Heads up')
            ->line('The TripJack account balance is ₹'.number_format($this->totalBalance, 2).', below the alert level of ₹'.number_format($this->threshold, 2).'.')
            ->line('Every flight and hotel booking is paid from this balance — once it runs out, bookings fail after the guest has paid and are refunded. Please top it up.')
            ->line('Detected at: '.$this->detectedAt->format('d M Y, H:i'))
            ->action('Open Admin', url('/tyt-console'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'TripJack balance is low',
            'body' => 'Balance ₹'.number_format($this->totalBalance, 2).' — below ₹'.number_format($this->threshold, 2).'. Top up to keep bookings working.',
            'total_balance' => $this->totalBalance,
            'threshold' => $this->threshold,
            'detected_at' => $this->detectedAt->toDateTimeString(),
        ];
    }
}
