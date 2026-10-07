<?php

namespace App\Http\Controllers;

use App\Models\{Ticket, TicketPriority, TicketStatus};
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $r)
    {
        $user = $r->user();
        $isAdmin = $user->role === 'admin';

        // admin: everything, support: tickets assigned to them, employee: tickets they created
        $mine = function () use ($user, $isAdmin) {
            $q = Ticket::query();

            return match (true) {
                $isAdmin => $q,
                $user->role === 'support' => $q->where('assigned_to', $user->id),
                default => $q->where('created_by', $user->id),
            };
        };

        $byStatus = TicketStatus::all()->mapWithKeys(
            fn ($s) => [$s->name => $mine()->where('status_id', $s->id)->count()]
        );
        $byPriority = TicketPriority::all()->mapWithKeys(
            fn ($p) => [$p->name => $mine()->where('priority_id', $p->id)->count()]
        );

        $overdue = $mine()->where('status_id', '<', Ticket::RESOLVED)
            ->whereNotNull('due_at')->where('due_at', '<', now())->count();

                    $recent = $mine()->with(['status', 'priority', 'technician'])->latest()->take(5)->get();

        $unassigned = $isAdmin
            ? Ticket::whereNull('assigned_to')->where('status_id', '<', Ticket::RESOLVED)->count()
            : null;

        return view('dashboard', [
            'byStatus' => $byStatus,
            'byPriority' => $byPriority,
            'total' => $byStatus->sum(),
            'overdue' => $overdue,
            'unassigned' => $unassigned,
                        'recent' => $recent,
        ]);
    }
}