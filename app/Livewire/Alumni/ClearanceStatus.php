<?php

namespace App\Livewire\Alumni;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ClearanceStatus extends Component
{
    public function render()
    {
        $user = Auth::user();
        $alumni = $user?->alumni;
        
        $yearOfGraduation = $alumni->year_of_graduation ?? null;
        $requiresClearance = $yearOfGraduation && $yearOfGraduation >= 2025;

        return view('livewire.alumni.clearance-status', [
            'alumni' => $alumni,
            'requiresClearance' => $requiresClearance,
        ])->layout('layouts.alumni');
    }
}
