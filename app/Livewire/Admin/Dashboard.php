<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function getUserStats()
    {
        return [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'suspended' => User::where('status', 'suspended')->count(),
            'new_today' => User::whereDate('created_at', today())->count()
        ];
    }

    public function render()
    {
        $stats = $this->getUserStats();
        return view('livewire.admin.dashboard', [
            'stats' => $stats
        ]);
    }
} 