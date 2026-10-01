<?php

namespace App\Http\Controllers;

use App\Models\{Ticket, TicketPriority, TicketStatus};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $r)
    {
        $mine = fn () => Ticket::visibleTo($r->user());

        $byStatus = TicketStatus::all()->mapWithKeys(
            fn ($s) => [$s->name => $mine()->where('status_id', $s->id)->count()]
        );
        $byPriority = TicketPriority::all()->mapWithKeys(
            fn ($p) => [$p->name => $mine()->where('priority_id', $p->id)->count()]
        );

        $overdue = $mine()->where('status_id', '<', Ticket::RESOLVED)
            ->whereNotNull('due_at')->where('due_at', '<', now())->count();

        $unassigned = $r->user()->isStaff()
            ? Ticket::whereNull('assigned_to')->where('status_id', '<', Ticket::RESOLVED)->count()
            : null;

        return view('dashboard', [
            'byStatus' => $byStatus,
            'byPriority' => $byPriority,
            'total' => $byStatus->sum(),
            'overdue' => $overdue,
            'unassigned' => $unassigned,
        ]);
    }
}