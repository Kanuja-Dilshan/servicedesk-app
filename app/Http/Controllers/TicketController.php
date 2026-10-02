<?php
namespace App\Http\Controllers;

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
            ->latest()->paginate(15)->withQueryString();

        return view('tickets.index', ['tickets' => $tickets, 'statuses' => TicketStatus::all()]);
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

    Notification::send(
        User::whereIn('role', ['support', 'admin'])->get(),
        new TicketActivity($ticket, "New ticket #{$ticket->id}: {$ticket->title}")
    );

    return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket created.');
}

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);
        $ticket->load(['comments.user', 'attachments', 'status', 'priority', 'category', 'creator', 'technician', 'activities.user']);

        return view('tickets.show', [
            'ticket' => $ticket,
            'statuses' => TicketStatus::all(),
            'technicians' => User::whereIn('role', ['support', 'admin'])->get(),
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
    $ticket->refresh()->load('status');

    if ($ticket->assigned_to && (int) $ticket->assigned_to !== $oldAssignee && $ticket->assigned_to !== $r->user()->id) {
        $ticket->technician->notify(new TicketActivity($ticket, "Ticket #{$ticket->id} was assigned to you"));
    }
    if ((int) $ticket->status_id !== $oldStatus && $ticket->created_by !== $r->user()->id) {
        $ticket->creator->notify(new TicketActivity($ticket, "Ticket #{$ticket->id} is now {$ticket->status->name}"));
    }

    return back()->with('status', 'Ticket updated.');
}
}
