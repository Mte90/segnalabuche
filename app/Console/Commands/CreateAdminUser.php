<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create {name?} {email?} {password=admin}';

    protected $description = 'Create an admin user for the application';

    public function handle(): int
    {
        $name = $this->argument('name') ?? 'Admin';
        $email = $this->argument('email') ?? 'admin@comune.rieti.it';
        $password = $this->argument('password');

        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists.");

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        $this->info('Admin user created successfully!');
        $this->info("Email: {$email}");
        $this->info("Password: {$password}");

        return self::SUCCESS;
    }
}
