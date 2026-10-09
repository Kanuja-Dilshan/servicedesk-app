<?php

namespace App\Http\Controllers;

use App\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function show(Request $request, TicketAttachment $attachment)
    {
        $this->authorize('view', $attachment->ticket);

        $ext = strtolower(pathinfo($attachment->path, PATHINFO_EXTENSION) ?: pathinfo($attachment->original_name, PATHINFO_EXTENSION));

        // only these safe types are shown in the browser; anything else is downloaded
        $types = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain; charset=UTF-8',
            'log' => 'text/plain; charset=UTF-8',
        ];

        if ($request->boolean('download') || ! isset($types[$ext])) {
            return Storage::disk('local')->download($attachment->path, $attachment->original_name);
        }

        return Storage::disk('local')->response($attachment->path, $attachment->original_name, [
            'Content-Type' => $types[$ext],
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }
}