<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketActivity extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public Ticket $ticket, public string $message) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    // Bell notification is saved instantly; the email is sent by the queue worker
    public function viaConnections(): array
    {
        return ['database' => 'sync', 'mail' => config('queue.default')];
    }

    public function toDatabase($notifiable): array
    {
        return ['ticket_id' => $this->ticket->id, 'message' => $this->message];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Ticket #{$this->ticket->id}")
            ->line($this->message)
            ->action('View ticket', route('tickets.show', $this->ticket));
    }
}