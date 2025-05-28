<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true; // All authenticated users can view the index
    }

    public function viewAll(User $user)
    {
        return $user->hasRole(['administrator', 'alumni-relations-officer']);
    }

    public function view(User $user, Transaction $transaction)
    {
        return $user->hasRole('administrator') || 
               $user->hasRole('alumni-relations-officer') || 
               $user->id === $transaction->alumni->user_id;
    }

    public function verify(User $user, Transaction $transaction)
    {
        return $user->hasRole('administrator') || $user->hasRole('alumni-relations-officer');
    }
} 