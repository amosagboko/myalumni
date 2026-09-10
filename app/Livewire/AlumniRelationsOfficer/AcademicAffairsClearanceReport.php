<?php

namespace App\Livewire\AlumniRelationsOfficer;

use App\Livewire\Concerns\HasClearanceReport;
use App\Models\OnboardingSetting;
use Livewire\Component;

class AcademicAffairsClearanceReport extends Component
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
        return 'components.layouts.alumni-relations-officer';
    }

    protected function pageTitle(): string
    {
        return 'Clearance Report';
    }
}
