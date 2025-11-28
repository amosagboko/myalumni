<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class AlumniReport extends Component
{
    public $alumni;
    public $user;
    public $showPrintDialog = false;

    public function mount()
    {
        $this->user = Auth::user();
        $this->alumni = $this->user->alumni;

        if (!$this->alumni) {
            session()->flash('error', 'Alumni information not found. Please complete your profile first.');
            return redirect()->route('alumni.bio-data');
        }
    }

    public function printReport()
    {
        $this->showPrintDialog = true;
    }

    public function downloadPdf()
    {
        return redirect()->route('reports.download-pdf');
    }

    protected function computeStatuses(): array
    {
        $alumni = $this->alumni;
        $needsBioData = !$alumni || !$alumni->contact_address || !$alumni->phone_number || !$alumni->qualification_type;
        $activeFees = $alumni ? $alumni->getActiveFees() : collect([]);
        $unpaidFees = $activeFees->filter(function ($fee) { return !$fee->isPaid(); });
        $needsPayments = $alumni && $activeFees->isNotEmpty() && $unpaidFees->isNotEmpty();
        $allOk = !$needsBioData && !$needsPayments;

        return compact('needsBioData', 'needsPayments', 'allOk');
    }

    public function render()
    {
        $statuses = $this->computeStatuses();

        if (!$statuses['allOk']) {
            return view('livewire.reports-gate', array_merge($statuses, [
                'user' => $this->user,
                'alumni' => $this->alumni,
            ]))->layout('layouts.alumni', [ 'title' => 'Alumni Reports' ]);
        }

        return view('livewire.alumni-report', [
            'user' => $this->user,
            'alumni' => $this->alumni
        ])->layout('layouts.alumni', [
            'title' => 'Alumni Data Report'
        ]);
    }
}
