<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Alumni;
use App\Models\User;
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
        // PDF generation will be implemented here
    }

    public function render()
    {
        if (!$this->alumni) {
            return view('livewire.alumni-report', [
                'user' => $this->user,
                'alumni' => null
            ])->layout('layouts.alumni', [
                'title' => 'Alumni Data Report'
            ]);
        }

        return view('livewire.alumni-report', [
            'user' => $this->user,
            'alumni' => $this->alumni
        ])->layout('layouts.alumni', [
            'title' => 'Alumni Data Report'
        ]);
    }
}
