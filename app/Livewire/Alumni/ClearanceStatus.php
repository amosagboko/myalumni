<?php

namespace App\Livewire\Alumni;

use App\Services\Alumni\ClearanceStatusService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ClearanceStatus extends Component
{
    public function render(ClearanceStatusService $clearanceStatusService)
    {
        $user = Auth::user();

        return view('livewire.alumni.clearance-status', $clearanceStatusService->snapshot(
            $user,
            $user?->alumni
        ))->layout('layouts.alumni', [
            'title' => 'Clearance Status',
        ]);
    }
}
