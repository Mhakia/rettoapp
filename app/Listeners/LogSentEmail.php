<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;

class LogSentEmail
{
    public function handle(MessageSent $event): void
    {
        // Recorded from Symfony's Email object; proves the app attempted to send, not that it was delivered.
        $recipients = collect($event->message->getTo())
            ->map(fn ($address) => $address->getAddress())
            ->implode(', ');

        activity('email')
            ->withProperties([
                'mailable' => $event->data['__laravel_mailable'] ?? null,
                'recipients' => $recipients,
                'subject' => $event->message->getSubject(),
            ])
            ->event('sent')
            ->log('Email sent');
    }
}
