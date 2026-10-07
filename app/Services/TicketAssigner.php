<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TicketAssigner
{
    /** Gives the ticket to the support technician with the fewest active tickets. Returns null if there is none. */
    public function assign(Ticket $ticket): ?User
    {
        return DB::transaction(function () use ($ticket) {
            $tech = User::where('role', 'support')
                ->withCount(['assignedTickets as active_count' => fn ($q) => $q->where('status_id', '<', Ticket::RESOLVED)])
                ->orderBy('active_count')
                // tie-break: whoever was given a ticket the longest time ago (never-assigned first)
                ->orderByRaw('(select max(created_at) from ticket_assignments where technician_id = users.id) asc')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $tech) {
                return null;
            }

            // events off: the audit entry below is written as "System" instead of the requester
            Ticket::withoutEvents(fn () => $ticket->update(['assigned_to' => $tech->id, 'status_id' => Ticket::ASSIGNED]));
            $ticket->assignments()->create(['technician_id' => $tech->id, 'assigned_by' => null]);
            $ticket->activities()->create(['user_id' => null, 'description' => "Auto-assigned to {$tech->name}"]);

            return $tech;
        });
    }
}