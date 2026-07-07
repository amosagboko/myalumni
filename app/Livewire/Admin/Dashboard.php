<?php

namespace App\Livewire\Admin;

use App\Models\AlumniYear;
use App\Models\OnboardingSetting;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public string $paymentYear = '';

    public function mount(): void
    {
        if ($this->paymentYear !== '') {
            return;
        }

        $this->paymentYear = (string) (
            AlumniYear::where('is_active', true)->value('year')
            ?? AlumniYear::orderByDesc('year')->value('year')
            ?? now()->year
        );
    }

    protected function paymentYearRecord(): ?AlumniYear
    {
        if ($this->paymentYear === '') {
            return null;
        }

        return AlumniYear::where('year', $this->paymentYear)->first();
    }

    protected function usersRegisteredInPeriod()
    {
        $year = $this->paymentYearRecord();

        if (!$year) {
            return User::query();
        }

        return User::whereBetween('created_at', [
            $year->start_date->copy()->startOfDay(),
            $year->end_date->copy()->endOfDay(),
        ]);
    }

    public function getStatsProperty(): array
    {
        return [
            'users' => [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'suspended' => User::where('status', 'suspended')->count(),
                'new_in_period' => $this->usersRegisteredInPeriod()->count(),
            ],
            'onboarding_open' => OnboardingSetting::isEnabled(),
        ];
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'stats' => $this->stats,
            'paymentYears' => AlumniYear::orderByDesc('year')->get(),
            'paymentYearRecord' => $this->paymentYearRecord(),
        ]);
    }
}
