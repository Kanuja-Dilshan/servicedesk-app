<?php

namespace App\Http\Controllers\Api;

use App\Services\TicketAssigner;
use App\Http\Controllers\Controller;
use App\Models\{Ticket, TicketPriority, User};
use App\Notifications\TicketActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class TicketController extends Controller
{
    private const SLA_HOURS = ['Low' => 72, 'Medium' => 24, 'High' => 8, 'Critical' => 4];
    private const WITH = ['status', 'priority', 'category', 'creator:id,name', 'technician:id,name'];

    public function index(Request $r)
    {
        return Ticket::with(self::WITH)
            ->visibleTo($r->user())
            ->search($r->query('q'))
            ->when($r->query('status'), fn ($q, $s) => $q->where('status_id', $s))
            ->latest()->paginate(15)->withQueryString();
    }

       public function store(Request $r)
    {
        $data = $r->validate([
            'title' => 'required|string|max:150',
            'description' => 'required|string|max:5000',
            'category_id' => 'required|exists:ticket_categories,id',
            'priority_id' => 'required|exists:ticket_priorities,id',
        ]);
        $priority = TicketPriority::find($data['priority_id']);

        $ticket = Ticket::create($data + [
            'created_by' => $r->user()->id,
            'status_id' => Ticket::OPEN,
            'due_at' => now()->addHours(self::SLA_HOURS[$priority->name] ?? 24),
        ]);

        $tech = app(TicketAssigner::class)->assign($ticket);

        if ($tech) {
            $tech->notify(new TicketActivity($ticket, "New ticket #{$ticket->id} was assigned to you: {$ticket->title}"));
        } else {
            Notification::send(
                User::whereIn('role', ['support', 'admin'])->get(),
                new TicketActivity($ticket, "New ticket #{$ticket->id}: {$ticket->title}")
            );
        }

        return response()->json($ticket->load(self::WITH), 201);
    }
    
    public function show(Request $r, Ticket $ticket)
    {
        $this->authorize('view', $ticket);
        $ticket->load(self::WITH);
        $ticket->setRelation(
            'comments',
            $ticket->comments()->with('user:id,name')
                ->when(! $r->user()->isStaff(), fn ($q) => $q->where('is_internal', false))
                ->get()
        );

        return $ticket;
    }

    public function update(Request $r, Ticket $ticket)
    {
        $this->authorize('manage', $ticket);
        $data = $r->validate([
            'status_id' => 'required|exists:ticket_statuses,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $oldStatus = (int) $ticket->status_id;
        $oldAssignee = (int) $ticket->assigned_to;

        if (! empty($data['assigned_to']) && (int) $data['assigned_to'] !== $oldAssignee) {
            $ticket->assignments()->create(['technician_id' => $data['assigned_to'], 'assigned_by' => $r->user()->id]);
            if ($oldStatus === Ticket::OPEN && (int) $data['status_id'] === Ticket::OPEN) {
                $data['status_id'] = Ticket::ASSIGNED;
            }
        }
        $data['resolved_at'] = (int) $data['status_id'] >= Ticket::RESOLVED ? ($ticket->resolved_at ?? now()) : null;

        $ticket->update($data);
        $ticket->refresh()->load('status');

        if ($ticket->assigned_to && (int) $ticket->assigned_to !== $oldAssignee && $ticket->assigned_to !== $r->user()->id) {
            $ticket->technician->notify(new TicketActivity($ticket, "Ticket #{$ticket->id} was assigned to you"));
        }
        if ((int) $ticket->status_id !== $oldStatus && $ticket->created_by !== $r->user()->id) {
            $ticket->creator->notify(new TicketActivity($ticket, "Ticket #{$ticket->id} is now {$ticket->status->name}"));
        }

        return $ticket->load(self::WITH);
    }

    public function comment(Request $r, Ticket $ticket)
    {
        $this->authorize('comment', $ticket);
        $data = $r->validate(['body' => 'required|string|max:3000', 'is_internal' => 'boolean']);
        $internal = $r->user()->isStaff() && $r->boolean('is_internal');

        $comment = $ticket->comments()->create([
            'user_id' => $r->user()->id,
            'body' => $data['body'],
            'is_internal' => $internal,
        ]);

        if (! $internal) {
            $target = $r->user()->id === $ticket->created_by ? $ticket->technician : $ticket->creator;
            $target?->notify(new TicketActivity($ticket, "{$r->user()->name} commented on ticket #{$ticket->id}"));
        }

        return response()->json($comment, 201);
    }
}