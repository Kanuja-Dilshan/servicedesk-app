<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketActivity extends Notification
{
    public function __construct(public Ticket $ticket, public string $message) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
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