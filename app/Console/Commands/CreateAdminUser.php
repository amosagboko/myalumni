<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    protected $signature = 'create:admin {email} {password}';
    protected $description = 'Create an administrator user';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        if (User::where('email', $email)->exists()) {
            $this->error('User with this email already exists!');
            return 1;
        }

        $user = User::create([
            'uuid' => Str::uuid(),
            'name' => 'Administrator',
            'email' => $email,
            'password' => Hash::make($password),
            'status' => 'active'
        ]);

        $user->assignRole('administrator');

        $this->info('Administrator user created successfully!');
        return 0;
    }
} 