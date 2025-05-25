<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FeeTemplatePermissionSeeder extends Seeder
{
    public function run()
    {
        // Create permissions
        $permissions = [
            'view fee templates',
            'create fee templates',
            'edit fee templates',
            'delete fee templates',
            'activate fee templates',
            'view fee template details'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to administrator role
        $adminRole = Role::findByName('administrator');
        if ($adminRole) {
            $adminRole->syncPermissions($permissions);
            $this->command->info('Fee template permissions assigned to administrator role.');
        } else {
            $this->command->error('Administrator role not found!');
        }
    }
} 