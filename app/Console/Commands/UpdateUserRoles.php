<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class UpdateUserRoles extends Command
{
    protected $signature = 'users:update-roles';
    protected $description = 'Update existing users with the new role system';

    public function handle()
    {
        // Get the administrator role
        $adminRole = Role::findByName('administrator');

        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // If user has no roles, assign them the alumni role
            if ($user->roles->isEmpty()) {
                $user->assignRole('alumni');
                $this->info("Assigned 'alumni' role to user: {$user->email}");
            }

            // If user is the first user (assuming it's the admin), assign administrator role
            if ($user->id === 1) {
                $user->assignRole('administrator');
                $this->info("Assigned 'administrator' role to user: {$user->email}");
            }
        }

        $this->info('User roles have been updated successfully!');
        return 0;
    }
} 