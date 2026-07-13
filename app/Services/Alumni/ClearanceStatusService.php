<?php

namespace App\Services\Alumni;

use App\Models\Alumni;
use App\Models\User;

class ClearanceStatusService
{
    public const DIVISION_CLEARANCE_FROM_YEAR = 2025;

    public function __construct(
        private readonly ClearanceFormService $clearanceFormService
    ) {}

    public function requiresDivisionClearance(?Alumni $alumni): bool
    {
        if (! $alumni?->year_of_graduation) {
            return false;
        }

        return (int) $alumni->year_of_graduation >= self::DIVISION_CLEARANCE_FROM_YEAR;
    }

    public function snapshot(?User $user, ?Alumni $alumni): array
    {
        $portal = $this->clearanceFormService->accessStatus($alumni);
        $requiresDivisionClearance = $this->requiresDivisionClearance($alumni);

        $studentAffairsCleared = (bool) ($alumni?->student_affairs_cleared ?? false);
        $academicAffairsCleared = (bool) ($alumni?->academic_affairs_cleared ?? false);
        $divisionsCleared = $studentAffairsCleared && $academicAffairsCleared;

        $overall = $this->resolveOverallState(
            $alumni,
            $portal,
            $requiresDivisionClearance,
            $divisionsCleared
        );

        return [
            'user' => $user,
            'alumni' => $alumni,
            'hasAlumniRecord' => (bool) $alumni,
            'requiresDivisionClearance' => $requiresDivisionClearance,
            'portal' => $portal,
            'studentAffairsCleared' => $studentAffairsCleared,
            'academicAffairsCleared' => $academicAffairsCleared,
            'divisionsCleared' => $divisionsCleared,
            'canAccessClearanceForm' => $portal['allOk'],
            'overall' => $overall,
            'divisionClearanceFromYear' => self::DIVISION_CLEARANCE_FROM_YEAR,
        ];
    }

    private function resolveOverallState(
        ?Alumni $alumni,
        array $portal,
        bool $requiresDivisionClearance,
        bool $divisionsCleared
    ): array {
        if (! $alumni) {
            return [
                'state' => 'no_record',
                'label' => 'No alumni record',
                'badgeClass' => 'bg-secondary',
                'message' => 'Complete your alumni profile to view clearance status.',
            ];
        }

        if (! $portal['allOk']) {
            return [
                'state' => 'pending_portal',
                'label' => 'Portal requirements pending',
                'badgeClass' => 'bg-warning text-dark',
                'message' => 'Complete your bio-data and payments before division clearance can be finalized.',
            ];
        }

        if (! $requiresDivisionClearance) {
            return [
                'state' => 'not_required',
                'label' => 'Division clearance not required',
                'badgeClass' => 'bg-info',
                'message' => 'Clearance by Student Affairs and Academic Affairs applies to alumni graduating in '
                    .self::DIVISION_CLEARANCE_FROM_YEAR.' or later.',
            ];
        }

        if (! $divisionsCleared) {
            return [
                'state' => 'pending_divisions',
                'label' => 'Division clearance pending',
                'badgeClass' => 'bg-warning text-dark',
                'message' => 'Your clearance form is available, but one or both university divisions have not marked you cleared yet.',
            ];
        }

        return [
            'state' => 'cleared',
            'label' => 'Fully cleared',
            'badgeClass' => 'bg-success',
            'message' => 'You have met all portal requirements and both divisions have marked you cleared.',
        ];
    }
}
