<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class TripJackRateLimitAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $count,
        public int $windowMinutes,
        public Carbon $detectedAt,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('TripJack rate limiting detected')
            ->greeting('Heads up')
            ->line("TripJack returned {$this->count} rate-limit (429) responses in the last {$this->windowMinutes} minutes.")
            ->line('Guests are unaffected right now — the site automatically retries and falls back gracefully — but this is worth a look if it keeps happening.')
            ->line('Detected at: '.$this->detectedAt->format('d M Y, H:i'))
            ->action('Open Admin', url('/tyt-console'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'TripJack rate limiting detected',
            'body' => "{$this->count} rate-limit (429) responses in the last {$this->windowMinutes} minutes.",
            'count' => $this->count,
            'window_minutes' => $this->windowMinutes,
            'detected_at' => $this->detectedAt->toDateTimeString(),
        ];
    }
}
