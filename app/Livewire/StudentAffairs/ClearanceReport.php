<?php

namespace App\Livewire\StudentAffairs;

use App\Livewire\Concerns\HasClearanceReport;
use App\Models\OnboardingSetting;
use Livewire\Component;

class ClearanceReport extends Component
{
    use HasClearanceReport;

    protected function division(): string
    {
        return OnboardingSetting::DIVISION_STUDENT_AFFAIRS;
    }

    protected function clearedColumn(): string
    {
        return 'student_affairs_cleared';
    }

    protected function layoutName(): string
    {
        return 'layouts.student-affairs';
    }

    protected function pageTitle(): string
    {
        return 'Student Affairs Clearance Report';
    }
}
