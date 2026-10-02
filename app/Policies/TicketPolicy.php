<?php

namespace App\Policies;

use App\Models\{Ticket, User};

class TicketPolicy
{
    public function view(User $u, Ticket $t): bool { return $u->isStaff() || $t->created_by === $u->id; }
    public function comment(User $u, Ticket $t): bool { return $this->view($u, $t); }
    public function manage(User $u, Ticket $t): bool { return $u->isStaff(); } // assign / change status

    // The requester confirms or rejects a fix, only while the ticket is Resolved
    public function respond(User $u, Ticket $t): bool
    {
        return $t->created_by === $u->id && (int) $t->status_id === Ticket::RESOLVED;
    }
}