<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewAccountRequest extends Notification
{
    public function __construct(
        public string $name,
        public string $requestedLabel,
    ) {}

    /** Saved in the database (survives refresh) and pushed live to the browser. */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'account_request',
            'text' => "{$this->name} requested an account ({$this->requestedLabel}) and is waiting for approval.",
            'url'  => route('users.index', [], false),
            'at'   => now()->toIso8601String(),
        ];
    }

    /** "sync" = send now, so no queue worker is needed. */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return (new BroadcastMessage($this->toArray($notifiable)))->onConnection('sync');
    }
}