<?php

namespace App\Http\Controllers;

use App\Models\TicketAttachment;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function show(TicketAttachment $attachment)
    {
        $this->authorize('view', $attachment->ticket);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name);
    }
}