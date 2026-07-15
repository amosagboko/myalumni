<?php

namespace App\Policies;

use App\Models\Election;
use App\Models\User;

class ElectionPolicy
{
    public function view(User $user, Election $election): bool
    {
        return $user->hasAnyRole(['elcom', 'elcom-chairman', 'administrator', 'alumni', 'alumni-president']);
    }

    public function update(User $user, Election $election): bool
    {
        return $this->manage($user) && $election->isMutable();
    }

    public function manage(User $user): bool
    {
        return $user->hasAnyRole(['elcom', 'elcom-chairman', 'administrator', 'alumni-president']);
    }

    public function archive(User $user, Election $election): bool
    {
        return $this->manage($user) && $election->canArchive();
    }

    public function startCycle(User $user): bool
    {
        return $this->manage($user) && Election::canStartNewCycle();
    }
}
