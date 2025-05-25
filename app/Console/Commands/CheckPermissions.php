<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CheckPermissions extends Command
{
    protected $signature = 'permissions:check {role?}';
    protected $description = 'Check permissions assigned to a role';

    public function handle()
    {
        $roleName = $this->argument('role') ?? 'administrator';
        $role = Role::findByName($roleName);

        if (!$role) {
            $this->error("Role '{$roleName}' not found!");
            return 1;
        }

        $this->info("Permissions for role '{$roleName}':");
        $this->newLine();

        $permissions = $role->permissions->pluck('name')->sort();
        foreach ($permissions as $permission) {
            $this->line("- {$permission}");
        }

        return 0;
    }
} 