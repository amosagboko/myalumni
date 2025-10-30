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

        return view('livewire.alumni.clearance-status', [
            'alumni' => $alumni,
        ])->layout('layouts.alumni');
    }
}
