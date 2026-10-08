<?php
namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use App\Services\TicketAssigner;
use App\Notifications\TicketActivity;
use Illuminate\Support\Facades\Notification;
use App\Models\{Ticket, TicketCategory, TicketPriority, TicketStatus, User};
use Illuminate\Http\Request;

class TicketController extends Controller
{
        public function index(Request $r)
    {
        $tickets = Ticket::with(['status', 'priority', 'category', 'technician'])
            ->visibleTo($r->user())
            ->search($r->query('q'))
            ->when($r->query('status'), fn ($q, $s) => $q->where('status_id', $s))
            ->when($r->query('priority'), fn ($q, $p) => $q->where('priority_id', $p))
            ->when($r->boolean('mine'), fn ($q) => $q->assignedTo($r->user()->id))
            ->latest()->paginate(15)->withQueryString();

        return view('tickets.index', [
            'tickets' => $tickets,
            'statuses' => TicketStatus::all(),
            'priorities' => TicketPriority::all(),
        ]);
    }

    public function create()
    {
        return view('tickets.create', ['categories' => TicketCategory::all(), 'priorities' => TicketPriority::all()]);
    }

    public function store(Request $r)
{
    $data = $r->validate([
        'title' => 'required|string|max:150',
        'description' => 'required|string|max:5000',
        'category_id' => 'required|exists:ticket_categories,id',
        'priority_id' => 'required|exists:ticket_priorities,id',
        'files.*' => 'file|max:4096|mimes:png,jpg,jpeg,pdf,txt,log',
    ]);

    $sla = ['Low' => 72, 'Medium' => 24, 'High' => 8, 'Critical' => 4]; // hours to resolve
    $priority = TicketPriority::find($data['priority_id']);

    $ticket = Ticket::create($data + [
        'created_by' => $r->user()->id,
        'status_id' => Ticket::OPEN,
        'due_at' => now()->addHours($sla[$priority->name] ?? 24),
    ]);

        foreach ($r->file('files', []) as $file) {
        $ticket->attachments()->create([
            'user_id' => $r->user()->id,
            'path' => $file->store("tickets/{$ticket->id}", 'local'),
            'original_name' => $file->getClientOriginalName(),
        ]);
    }

    $tech = app(TicketAssigner::class)->assign($ticket);

    if ($tech) {
        $tech->notify(new TicketActivity($ticket, "New ticket #{$ticket->id} was assigned to you: {$ticket->title}"));
    } else {
        Notification::send(
            User::whereIn('role', ['support', 'admin'])->get(),
            new TicketActivity($ticket, "New ticket #{$ticket->id}: {$ticket->title}")
        );
    }

    return redirect()->route('tickets.show', $ticket)
        ->with('status', $tech ? "Ticket created and assigned to {$tech->name}." : 'Ticket created.');
}

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);
        $ticket->load(['comments.user', 'attachments', 'status', 'priority', 'category', 'creator', 'technician', 'activities.user']);

                return view('tickets.show', [
            'ticket' => $ticket,
            'statuses' => TicketStatus::all(),
            'technicians' => User::whereIn('role', ['support', 'admin'])->get(),
            'delegates' => User::where('role', 'technician')->orderBy('name')->get(),
        ]);
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
        if ((int) $ticket->status_id >= Ticket::RESOLVED && $ticket->review_requested_at) {
        $ticket->forceFill(['review_requested_at' => null])->saveQuietly();
    }
    $ticket->refresh()->load('status');

    if ($ticket->assigned_to && (int) $ticket->assigned_to !== $oldAssignee && $ticket->assigned_to !== $r->user()->id) {
        $ticket->technician->notify(new TicketActivity($ticket, "Ticket #{$ticket->id} was assigned to you"));
    }
    if ((int) $ticket->status_id !== $oldStatus && $ticket->created_by !== $r->user()->id) {
        $ticket->creator->notify(new TicketActivity($ticket, "Ticket #{$ticket->id} is now {$ticket->status->name}"));
    }

    return back()->with('status', 'Ticket updated.');
}

    public function close(Ticket $ticket)
{
    $this->authorize('respond', $ticket);
    $ticket->update(['status_id' => Ticket::CLOSED]);
    $ticket->technician?->notify(
        new TicketActivity($ticket, "Ticket #{$ticket->id} was confirmed fixed and closed by the requester")
    );

    return back()->with('status', 'Ticket closed. Thanks for confirming!');
}

public function reopen(Request $r, Ticket $ticket)
{
    $this->authorize('respond', $ticket);
    $data = $r->validate(['reason' => 'required|string|max:1000']);

    $ticket->update([
        'status_id' => $ticket->assigned_to ? Ticket::IN_PROGRESS : Ticket::OPEN,
        'resolved_at' => null,
    ]);
    $ticket->comments()->create([
        'user_id' => $r->user()->id,
        'body' => "Reopened: {$data['reason']}",
        'is_internal' => false,
    ]);

    $message = "Ticket #{$ticket->id} was reopened by the requester";
    if ($ticket->technician) {
        $ticket->technician->notify(new TicketActivity($ticket, $message));
    } else {
        Notification::send(User::whereIn('role', ['support', 'admin'])->get(), new TicketActivity($ticket, $message));
    }

    return back()->with('status', 'Ticket reopened.');
}  

    /** IT Support hands the ticket to a technician, with a note for them. */
    public function delegate(Request $r, Ticket $ticket)
    {
        $this->authorize('manage', $ticket);

        if ((int) $ticket->status_id >= Ticket::RESOLVED) {
            return back()->with('error', 'Reopen the ticket before assigning it to a technician.');
        }

        $data = $r->validate([
            'technician_id' => ['required', Rule::exists('users', 'id')->where('role', 'technician')],
            'note' => 'required|string|max:2000',
        ]);
        $tech = User::findOrFail($data['technician_id']);
        $me = $r->user();

        Ticket::withoutEvents(fn () => $ticket->forceFill([
            'assigned_to' => $tech->id,
            'status_id' => Ticket::ASSIGNED,
            'delegated_by' => $me->id,
            'review_requested_at' => null,
        ])->save());

        $ticket->assignments()->create(['technician_id' => $tech->id, 'assigned_by' => $me->id]);
        $ticket->comments()->create(['user_id' => $me->id, 'body' => $data['note'], 'is_internal' => true]);
        $ticket->activities()->create(['user_id' => $me->id, 'description' => "Assigned to technician {$tech->name}"]);
        $tech->notify(new TicketActivity($ticket, "Ticket #{$ticket->id} was assigned to you by {$me->name}: {$ticket->title}"));

        return back()->with('status', "Ticket assigned to {$tech->name}.");
    }

    /** The technician starts working: status becomes In Progress. */
    public function start(Request $r, Ticket $ticket)
    {
        $this->authorize('work', $ticket);

        Ticket::withoutEvents(fn () => $ticket->forceFill(['status_id' => Ticket::IN_PROGRESS])->save());
        $ticket->activities()->create(['user_id' => $r->user()->id, 'description' => 'Started working on this ticket']);
        $ticket->creator->notify(new TicketActivity($ticket, "Ticket #{$ticket->id} is now In Progress"));

        return back()->with('status', 'Marked as In Progress.');
    }

    /** The technician finishes: the ticket goes back to the IT Support person who assigned it. */
    public function complete(Request $r, Ticket $ticket)
    {
        $this->authorize('work', $ticket);
        $data = $r->validate(['note' => 'required|string|max:2000']);
        $me = $r->user();

        $back = User::whereIn('role', ['support', 'admin'])->find($ticket->delegated_by)
            ?? User::where('role', 'admin')->first();

        Ticket::withoutEvents(fn () => $ticket->forceFill([
            'assigned_to' => $back?->id,
            'status_id' => Ticket::IN_PROGRESS,
            'review_requested_at' => now(),
        ])->save());

        $ticket->comments()->create(['user_id' => $me->id, 'body' => "Work completed: {$data['note']}", 'is_internal' => true]);
        $ticket->activities()->create([
            'user_id' => $me->id,
            'description' => $back
                ? "Work completed by {$me->name}, returned to {$back->name} for review"
                : "Work completed by {$me->name}",
        ]);
        $back?->notify(new TicketActivity($ticket, "Ticket #{$ticket->id}: {$me->name} finished the work. Please review it."));

        return redirect()->route('dashboard')->with('status', 'Thanks! The ticket was sent back to IT Support for review.');
    }

}
