<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Alumni;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

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
        if (!$this->alumni) {
            session()->flash('error', 'Alumni information not found.');
            return;
        }

        $data = [
            'user' => $this->user,
            'alumni' => $this->alumni,
            'generatedAt' => now(),
        ];

        $html = view('pdf.alumni-report', $data)->render();

        $pdf = Pdf::loadHTML($html)->setPaper('a4');

        $fileName = 'alumni_report_'.str_replace(' ', '_', strtolower($this->user->name)).'_'.now()->format('Ymd_His').'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName, [
            'Content-Type' => 'application/pdf'
        ]);
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
