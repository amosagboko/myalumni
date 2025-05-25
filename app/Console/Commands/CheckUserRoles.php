<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CheckUserRoles extends Command
{
    protected $signature = 'users:check-roles';
    protected $description = 'Check and display user roles';

    public function handle()
    {
        $users = User::all();

        $this->info('Checking user roles...');
        $this->newLine();

        foreach ($users as $user) {
            $this->info("User: {$user->name} ({$user->email})");
            $this->info("ID: {$user->id}");
            $this->info("Roles: " . $user->roles->pluck('name')->implode(', '));
            $this->newLine();
        }

        if ($this->confirm('Do you want to assign the administrator role to a user?')) {
            $userId = $this->ask('Enter the user ID:');
            $user = User::find($userId);

            if ($user) {
                $user->assignRole('administrator');
                $this->info("Administrator role assigned to {$user->name}");
            } else {
                $this->error("User not found!");
            }
        }

        return 0;
    }
} 