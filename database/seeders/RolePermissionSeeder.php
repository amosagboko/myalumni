<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions
        $permissions = [
            'suspend users account',
            'create event',
            'view all users',
            'create election',
            'moderate post or delete',
            'upload alumni',
            'create all users',
            'create fee template',
            'create post',
            'comment on post',
            'view events',
	        'view alumni',
            'update profile',
            'request transcript',
            'generate reports',
            'fee template',
            'chat',
            'activate alumni',
            'membership',
            'message',
            'my transactions',
            'my donations',
	        'job post',
        ];
    

    // Create permissions
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    // Define roles and assign permissions
    $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
    $supportAdmin = Role::firstOrCreate(['name' => 'support-admin']);
    $alumni = Role::firstOrCreate(['name' => 'alumni']);
    $administrator = Role::firstOrCreate(['name' => 'administrator']);
    $elcomChairman = Role::firstOrCreate(['name' => 'elcom-chairman']);
    $alumniRelationsOfficer = Role::firstOrCreate(['name' => 'alumni-relations-officer']);


    
    // Assign permissions to roles
    $superAdmin->givePermissionTo([
            'suspend users account',
            'create event',
            'view all users',
            'create election',
            'moderate post or delete',
            'upload alumni',
            'create all users',
            'create fee template',
            'create post',
            'comment on post',
            'view events',
	        'view alumni',
            'update profile',
            'request transcript',
            'generate reports',
            'fee template',
            'chat',
            'activate alumni',
            'membership',
            'message',
            'my transactions',
            'my donations',
	        'job post',
    ]);

    $supportAdmin->givePermissionTo([
            'suspend users account',
            'create event',
            'upload alumni',
            'create election',
            'moderate post or delete',
            'generate reports',
            'chat',
    ]);

    $alumni->givePermissionTo([
        'request transcript',
        'activate alumni',
        'membership',
        'message',
        'my transactions',
        'my donations',
        'job post',
        'create election',
        'view events',
        'update profile',
        'view alumni',
        'create post',
        'comment on post',
        'chat',
        'view events',
        
    ]);

    $administrator->givePermissionTo([
            'suspend users account',
            'create event',
            'view all users',
            'create election',
            'moderate post or delete',
            'upload alumni',
            'create all users',
            'create fee template',
            'create post',
            'comment on post',
            'view events',
            'view alumni',
            'update profile',
            'request transcript',
            'generate reports',
            'fee template',
            'chat',
            'activate alumni',
            'membership',
            'message',
            'my transactions',
            'my donations',
            'job post',
    ]);

    $elcomChairman->givePermissionTo([
        'create election',
        'view events',
        'view alumni',
        'generate reports',
        'chat',
        'moderate post or delete'
    ]);

    $alumniRelationsOfficer->givePermissionTo([
        'create event',
        'view events',
        'view alumni',
        'upload alumni',
        'activate alumni',
        'generate reports',
        'chat',
        'moderate post or delete',
        'create post',
        'comment on post'
    ]);

    }

}
