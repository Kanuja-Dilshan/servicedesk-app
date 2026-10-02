<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Notifications\TicketActivity;
use Illuminate\Console\Command;

class AutoCloseTickets extends Command
{
    protected $signature = 'servicedesk:auto-close {--days=7}';
    protected $description = 'Close tickets that have stayed Resolved for N days without a response';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $tickets = Ticket::where('status_id', Ticket::RESOLVED)
            ->where('resolved_at', '<', now()->subDays($days))
            ->get();

        foreach ($tickets as $ticket) {
            $ticket->update(['status_id' => Ticket::CLOSED]); // audit log records this as "System"
            $ticket->creator->notify(new TicketActivity(
                $ticket, "Ticket #{$ticket->id} was closed automatically after {$days} days without a response"
            ));
        }

        $this->info("Closed {$tickets->count()} ticket(s).");

        return self::SUCCESS;
    }
}