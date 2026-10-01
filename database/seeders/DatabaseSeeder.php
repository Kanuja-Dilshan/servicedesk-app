<?php
namespace Database\Seeders;

use App\Models\{User, Department, TicketCategory, TicketStatus, TicketPriority};
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // IDs must match the constants in Ticket (1..6)
        foreach (['Open', 'Assigned', 'In Progress', 'Waiting for User', 'Resolved', 'Closed'] as $s) TicketStatus::create(['name' => $s]);
        foreach (['Low', 'Medium', 'High', 'Critical'] as $p) TicketPriority::create(['name' => $p]);
        foreach (['Hardware', 'Software', 'Network', 'Email', 'Access / Accounts'] as $c) TicketCategory::create(['name' => $c]);
        $dept = Department::create(['name' => 'Finance']);

        User::create(['name' => 'Admin', 'email' => 'admin@demo.test', 'password' => 'password', 'role' => 'admin']);
        User::create(['name' => 'Tech One', 'email' => 'tech@demo.test', 'password' => 'password', 'role' => 'support']);
        User::create(['name' => 'Emp One', 'email' => 'emp@demo.test', 'password' => 'password', 'role' => 'employee', 'department_id' => $dept->id]);
    }
}
