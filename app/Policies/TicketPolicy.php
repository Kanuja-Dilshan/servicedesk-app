<?php

namespace App\Policies;

use App\Models\{Ticket, User};

class TicketPolicy
{
    public function view(User $u, Ticket $t): bool
    {
        if ($u->isTechnician()) {
            return (int) $t->assigned_to === $u->id; // technicians only see tickets assigned to them
        }

        return $u->isStaff() || $t->created_by === $u->id;
    }

    public function comment(User $u, Ticket $t): bool { return $this->view($u, $t); }

    // IT Support and admin: assign / change status. Technicians use work() instead.
    public function manage(User $u, Ticket $t): bool { return $u->isStaff() && ! $u->isTechnician(); }

    // A technician can work only on an unresolved ticket that is assigned to them
    public function work(User $u, Ticket $t): bool
    {
        return $u->isTechnician() && (int) $t->assigned_to === $u->id && (int) $t->status_id < Ticket::RESOLVED;
    }

    // The requester confirms or rejects a fix, only while the ticket is Resolved
    public function respond(User $u, Ticket $t): bool
    {
        return $t->created_by === $u->id && (int) $t->status_id === Ticket::RESOLVED;
    }
}