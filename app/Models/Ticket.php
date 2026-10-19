<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Ticket extends Model
{
    public const OPEN = 1, ASSIGNED = 2, IN_PROGRESS = 3, WAITING = 4, RESOLVED = 5, CLOSED = 6;
        // Hours to resolve, by priority name. IT Support sets the priority; employees do not.
    public const SLA_HOURS = ['Low' => 72, 'Medium' => 24, 'High' => 8, 'Critical' => 4];
    public const DEFAULT_PRIORITY = 'Medium'; // placeholder until IT Support triages the ticket

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
        return match (true) {
            $u->isAdmin() || $u->role === 'support' => $q,
            $u->isTechnician() => $q->where('assigned_to', $u->id),
            default => $q->where('created_by', $u->id),
        };
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        return $term ? $q->where(fn ($w) => $w->where('title', 'like', "%$term%")->orWhere('description', 'like', "%$term%")) : $q;
    }

        public function scopeAssignedTo(Builder $q, int $userId): Builder
    {
        return $q->where('assigned_to', $userId);
    }

    public function activities() { return $this->hasMany(TicketActivity::class)->latest('id'); }

protected static function booted(): void
{
    static::created(fn (Ticket $t) => $t->log('Ticket created', $t->created_by));

    static::updated(function (Ticket $t) {
        if ($t->wasChanged('status_id')) {
            $t->log('Status changed to ' . TicketStatus::find($t->status_id)->name);
        }
                if ($t->wasChanged('priority_id')) {
            $t->log('Priority set to ' . TicketPriority::find($t->priority_id)?->name);
        }
        if ($t->wasChanged('assigned_to')) {
            $name = $t->assigned_to ? User::find($t->assigned_to)?->name : null;
            $t->log($name ? "Assigned to {$name}" : 'Unassigned');
        }
    });
}

public function log(string $description, ?int $userId = null): void
{
    // web requests use the default guard, API requests use the sanctum guard
    $userId ??= auth()->id() ?? auth('sanctum')->id();
    $this->activities()->create(['user_id' => $userId, 'description' => $description]);
}

}
