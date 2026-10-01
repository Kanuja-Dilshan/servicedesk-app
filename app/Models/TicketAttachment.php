<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketAttachment extends Model
{
    protected $fillable = ['ticket_id', 'user_id', 'path', 'original_name'];

    public function ticket()
{
    return $this->belongsTo(Ticket::class);
}
}

