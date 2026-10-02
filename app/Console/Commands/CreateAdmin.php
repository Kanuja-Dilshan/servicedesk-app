<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    protected $signature = 'servicedesk:create-admin {email} {name=Administrator}';
    protected $description = 'Create an admin user (password is asked interactively)';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (User::where('email', $email)->exists()) {
            $this->error("A user with {$email} already exists.");
            return self::FAILURE;
        }

        $password = (string) $this->secret('Password (min 12 characters)');
        if (strlen($password) < 12) {
            $this->error('Password must be at least 12 characters.');
            return self::FAILURE;
        }

        User::create([
            'name' => $this->argument('name'),
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
        ]);

        $this->info("Admin {$email} created.");
        return self::SUCCESS;
    }
}