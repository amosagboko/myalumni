<?php

namespace App\Services\Alumni;

use App\Models\Election;
use App\Models\User;
use App\Services\AlumniElectionParticipationService;

class AlumniElectionHubService
{
    public function __construct(
        private readonly AlumniElectionParticipationService $participationService
    ) {}

    public function hubData(?User $user): array
    {
        $alumni = $user?->alumni;

        $byElection = Election::query()
            ->where('election_type', 'by_election')
            ->where('is_active', true)
            ->whereNotIn('status', ['completed', 'archived'])
            ->with(['offices', 'parentElection'])
            ->first();

        $currentElection = $byElection ?? Election::query()
            ->where('is_active', true)
            ->where(function ($query) {
                $query->operational()
                    ->orWhere('status', 'incomplete');
            })
            ->with('offices')
            ->first();

        $parentElection = $byElection?->parentElection;

        $pastElections = Election::query()
            ->historical()
            ->when($currentElection, fn ($query) => $query->where('id', '!=', $currentElection->id))
            ->when($parentElection, fn ($query) => $query->where('id', '!=', $parentElection->id))
            ->with('offices')
            ->orderByDesc('election_year')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $participation = null;
        $phaseLabel = null;
        $actions = null;

        if ($currentElection && $alumni) {
            $participation = $this->participationService->participationFor($currentElection, $alumni);
            $phaseLabel = $this->participationService->phaseLabel($currentElection);
            $actions = $this->participationService->actionsFor($currentElection, $alumni, $participation);
        }

        return [
            'currentElection' => $currentElection,
            'pastElections' => $pastElections,
            'parentElection' => $parentElection,
            'participation' => $participation,
            'phaseLabel' => $phaseLabel,
            'phaseBadgeClass' => $this->phaseBadgeClass($phaseLabel),
            'actions' => $actions ?? [],
        ];
    }

    public function phaseBadgeClass(?string $phaseLabel): string
    {
        if (! $phaseLabel) {
            return 'bg-secondary';
        }

        $label = strtolower($phaseLabel);

        if (str_contains($label, 'voting')) {
            return 'bg-warning text-dark';
        }

        if (str_contains($label, 'accreditation')) {
            return 'bg-info';
        }

        if (str_contains($label, 'expression') || str_contains($label, 'eoi')) {
            return 'bg-success';
        }

        if (str_contains($label, 'incomplete') || str_contains($label, 'pending')) {
            return 'bg-warning text-dark';
        }

        if (str_contains($label, 'archived') || str_contains($label, 'completed')) {
            return 'bg-secondary';
        }

        return 'bg-primary';
    }

    public function eoiStatusBadgeClass(?string $status): string
    {
        return match ($status) {
            'approved' => 'bg-success',
            'rejected' => 'bg-danger',
            'paid_awaiting_screening' => 'bg-info',
            default => 'bg-warning text-dark',
        };
    }
}
