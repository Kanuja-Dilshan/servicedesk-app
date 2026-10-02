<?php

namespace Database\Seeders;

use App\Models\{TicketCategory, TicketPriority, TicketStatus};
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    public function run(): void
    {
        // IDs must match the constants in Ticket (1..6), so they are set explicitly.
        // Safe to run more than once.
        TicketStatus::unguarded(function () {
            foreach (['Open', 'Assigned', 'In Progress', 'Waiting for User', 'Resolved', 'Closed'] as $i => $name) {
                TicketStatus::updateOrCreate(['id' => $i + 1], ['name' => $name]);
            }
        });

        TicketPriority::unguarded(function () {
            foreach (['Low', 'Medium', 'High', 'Critical'] as $i => $name) {
                TicketPriority::updateOrCreate(['id' => $i + 1], ['name' => $name]);
            }
        });

        foreach (['Hardware', 'Software', 'Network', 'Email', 'Access / Accounts'] as $name) {
            TicketCategory::firstOrCreate(['name' => $name]);
        }
    }
}