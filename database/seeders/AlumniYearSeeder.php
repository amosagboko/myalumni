<?php

namespace Database\Seeders;

use App\Models\AlumniYear;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AlumniYearSeeder extends Seeder
{
    public function run()
    {
        // Create alumni years for the last 5 years
        $currentYear = Carbon::now()->year;
        
        for ($i = 0; $i < 5; $i++) {
            $year = $currentYear - $i;
            AlumniYear::create([
                'year' => $year,
                'start_date' => Carbon::create($year, 1, 1),
                'end_date' => Carbon::create($year, 12, 31),
                'is_active' => $i === 0, // Only the most recent year is active
            ]);
        }
    }
} 