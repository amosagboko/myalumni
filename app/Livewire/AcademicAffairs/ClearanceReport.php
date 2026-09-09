<?php

namespace App\Livewire\AcademicAffairs;

use App\Livewire\Concerns\HasClearanceReport;
use App\Models\OnboardingSetting;
use Livewire\Component;

class ClearanceReport extends Component
{
    use HasClearanceReport;

    protected function division(): string
    {
        return OnboardingSetting::DIVISION_ACADEMIC_AFFAIRS;
    }

    protected function clearedColumn(): string
    {
        return 'academic_affairs_cleared';
    }

    protected function layoutName(): string
    {
        return 'layouts.academic-affairs';
    }

    protected function pageTitle(): string
    {
        return 'Academic Affairs Clearance Report';
    }
}
