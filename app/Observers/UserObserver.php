<?php

namespace App\Observers;

use App\Models\User;
use Spatie\Permission\Models\Role;

class UserObserver
{
    public function created(User $user)
    {
        // No automatic role assignment
        // Roles should be explicitly assigned by administrators
    }
} 