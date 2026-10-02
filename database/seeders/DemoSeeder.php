<?php

namespace Database\Seeders;

use App\Models\{Department, User};
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $dept = Department::firstOrCreate(['name' => 'Finance']);

        User::firstOrCreate(['email' => 'admin@demo.test'], ['name' => 'Admin', 'password' => 'password', 'role' => 'admin']);
        User::firstOrCreate(['email' => 'tech@demo.test'], ['name' => 'Tech One', 'password' => 'password', 'role' => 'support']);
        User::firstOrCreate(['email' => 'emp@demo.test'], ['name' => 'Emp One', 'password' => 'password', 'role' => 'employee', 'department_id' => $dept->id]);
    }
}