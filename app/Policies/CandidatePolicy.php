<?php

namespace App\Policies;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CandidatePolicy
{
    use HandlesAuthorization;

    public function view(User $user, Candidate $candidate): bool
    {
        if ($user->hasRole(['administrator', 'elcom', 'elcom-chairman'])) {
            return true;
        }

        if ($user->hasRole('alumni')) {
            return $candidate->alumni_id === $user->alumni->id;
        }

        if ($user->hasRole('alumni-agent')) {
            return $candidate->isApprovedAgent($user);
        }

        return false;
    }

    public function update(User $user, Candidate $candidate): bool
    {
        return $user->hasRole(['administrator', 'elcom', 'elcom-chairman']);
    }

    public function delete(User $user, Candidate $candidate): bool
    {
        return $user->hasRole(['administrator', 'elcom', 'elcom-chairman']);
    }
}
