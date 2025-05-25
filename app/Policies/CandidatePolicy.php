<?php

namespace App\Policies;

use App\Models\Candidate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CandidatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the candidate.
     */
    public function view(User $user, Candidate $candidate): bool
    {
        // Allow if the user is an admin or ELCOM member
        if ($user->hasRole(['administrator', 'elcom'])) {
            return true;
        }

        // For alumni users, only allow if they own the candidate
        if ($user->hasRole('alumni')) {
            return $candidate->alumni_id === $user->alumni->id;
        }

        // For alumni agents, only allow if they are the approved agent
        if ($user->hasRole('alumni-agent')) {
            return $candidate->approved_agent_id === $user->alumni->id;
        }

        return false;
    }

    /**
     * Determine whether the user can update the candidate.
     */
    public function update(User $user, Candidate $candidate): bool
    {
        // Only allow if the user is an admin or ELCOM member
        return $user->hasRole(['administrator', 'elcom']);
    }

    /**
     * Determine whether the user can delete the candidate.
     */
    public function delete(User $user, Candidate $candidate): bool
    {
        // Only allow if the user is an admin or ELCOM member
        return $user->hasRole(['administrator', 'elcom']);
    }
} 