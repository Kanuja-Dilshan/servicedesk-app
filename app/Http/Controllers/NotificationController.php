<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $r)
    {
        return view('notifications.index', ['notifications' => $r->user()->notifications()->paginate(15)]);
    }

    public function open(Request $r, string $id)
    {
        $n = $r->user()->notifications()->findOrFail($id);
        $n->markAsRead();

        return redirect()->route('tickets.show', $n->data['ticket_id']);
    }
}