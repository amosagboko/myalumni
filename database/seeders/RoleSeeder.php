<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles if they don't exist
        $roles = [
            'administrator',
            'alumni',
            'alumni-relations-officer',
            'alumni-president',
            'alumni-electoral-officer',
            'alumni-agent',
            'elcom',
            'elcom-chairman'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create permissions if they don't exist
        $permissions = [
            // User management permissions
            'create users',
            'read users',
            'update users',
            'delete users',
            
            // Post management permissions
            'create posts',
            'read posts',
            'update posts',
            'delete posts',
            
            // Comment management permissions
            'create comments',
            'read comments',
            'update comments',
            'delete comments',
            
            // Event management permissions
            'create events',
            'read events',
            'update events',
            'delete events',
            
            // Election management permissions
            'create elections',
            'read elections',
            'update elections',
            'delete elections',
            
            // Electoral process permissions
            'manage electoral process',
            
            // Agent permissions
            'manage candidate'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $administrator = Role::findByName('administrator');
        $administrator->syncPermissions($permissions);

        $aro = Role::findByName('alumni-relations-officer');
        $aro->syncPermissions([
            'create users',
            'read users',
            'update users',
            'create posts',
            'read posts',
            'update posts',
            'delete posts',
            'create comments',
            'read comments',
            'update comments',
            'delete comments',
            'create events',
            'read events',
            'update events',
            'delete events',
            'manage electoral process'
        ]);

        $president = Role::findByName('alumni-president');
        $president->syncPermissions([
            'create elections',
            'read elections',
            'update elections',
            'delete elections'
        ]);

        $electoralOfficer = Role::findByName('alumni-electoral-officer');
        $electoralOfficer->syncPermissions([
            'read elections',
            'manage electoral process'
        ]);

        $agent = Role::findByName('alumni-agent');
        $agent->syncPermissions([
            'manage candidate'
        ]);

        $elcomChairman = Role::findByName('elcom-chairman');
        $elcomChairman->syncPermissions([
            'read elections',
            'manage electoral process',
            'create elections',
            'update elections',
            'delete elections'
        ]);

        $alumni = Role::findByName('alumni');
        $alumni->syncPermissions([
            'read posts',
            'create posts',
            'update posts',
            'delete posts',
            'read comments',
            'create comments',
            'update comments',
            'delete comments',
            'read events'
        ]);
    }
} 