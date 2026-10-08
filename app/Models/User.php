<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
   use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'department_id'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['email_verified_at' => 'datetime', 'password' => 'hashed']; }

    public function isAdmin(): bool { return $this->role === 'admin'; }
        public function isTechnician(): bool { return $this->role === 'technician'; }
    public function isStaff(): bool { return in_array($this->role, ['support', 'technician', 'admin']); }
    public function department() { return $this->belongsTo(Department::class); }
    public function tickets() { return $this->hasMany(Ticket::class, 'created_by'); }
    public function assignedTickets() { return $this->hasMany(Ticket::class, 'assigned_to'); }
}
