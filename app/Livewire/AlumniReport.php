<?php

namespace App\Livewire;

use App\Services\Alumni\ClearanceFormService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AlumniReport extends Component
{
    public $alumni;

    public $user;

    public function mount(ClearanceFormService $clearanceFormService): void
    {
        $this->user = Auth::user();
        $this->alumni = $this->user->alumni;

        if (! $this->alumni) {
            session()->flash('error', 'Alumni information not found. Please complete your profile first.');

            $this->redirect(route('alumni.bio-data'), navigate: true);

            return;
        }
    }

    public function render(ClearanceFormService $clearanceFormService)
    {
        $statuses = $clearanceFormService->accessStatus($this->alumni);

        if (! $statuses['allOk']) {
            return view('livewire.reports-gate', array_merge($statuses, [
                'user' => $this->user,
                'alumni' => $this->alumni,
            ]))->layout('layouts.alumni', ['title' => 'Clearance Form']);
        }

        return view('livewire.alumni-report', array_merge(
            $clearanceFormService->context($this->user, $this->alumni),
            ['user' => $this->user, 'alumni' => $this->alumni]
        ))->layout('layouts.alumni', [
            'title' => 'Clearance Form',
        ]);
    }
}
