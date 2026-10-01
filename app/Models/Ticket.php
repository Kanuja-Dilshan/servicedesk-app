<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Ticket extends Model
{
    public const OPEN = 1, ASSIGNED = 2, IN_PROGRESS = 3, WAITING = 4, RESOLVED = 5, CLOSED = 6;

    protected $fillable = ['title', 'description', 'category_id', 'priority_id', 'status_id', 'created_by', 'assigned_to', 'due_at', 'resolved_at'];
    protected function casts(): array { return ['due_at' => 'datetime', 'resolved_at' => 'datetime']; }

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function technician() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function category() { return $this->belongsTo(TicketCategory::class, 'category_id'); }
    public function priority() { return $this->belongsTo(TicketPriority::class, 'priority_id'); }
    public function status() { return $this->belongsTo(TicketStatus::class, 'status_id'); }
    public function comments() { return $this->hasMany(TicketComment::class)->latest(); }
    public function assignments() { return $this->hasMany(TicketAssignment::class); }
    public function attachments() { return $this->hasMany(TicketAttachment::class); }

    public function scopeVisibleTo(Builder $q, User $u): Builder
    {
        return $u->isStaff() ? $q : $q->where('created_by', $u->id);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        return $term ? $q->where(fn ($w) => $w->where('title', 'like', "%$term%")->orWhere('description', 'like', "%$term%")) : $q;
    }
}
