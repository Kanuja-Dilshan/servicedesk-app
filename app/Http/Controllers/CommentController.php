<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Notifications\TicketActivity;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $r, Ticket $ticket)
    {
        $this->authorize('comment', $ticket);
        $data = $r->validate(['body' => 'required|string|max:3000', 'is_internal' => 'boolean']);
        $internal = $r->user()->isStaff() && $r->boolean('is_internal');

        $ticket->comments()->create([
            'user_id' => $r->user()->id,
            'body' => $data['body'],
            'is_internal' => $internal,
        ]);

        if (! $internal) {
            $target = $r->user()->id === $ticket->created_by ? $ticket->technician : $ticket->creator;
            $target?->notify(new TicketActivity($ticket, "{$r->user()->name} commented on ticket #{$ticket->id}"));
        }

        return back()->with('status', 'Comment added.');
    }
}
