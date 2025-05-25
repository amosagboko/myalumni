<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ElectionOffice;
use App\Models\FeeType;

class UpdateElectionOfficesSeeder extends Seeder
{
    public function run()
    {
        // Pick a valid fee type (for example, one with code 'screening' or 'EOI')
        $validFeeType = FeeType::where('code', 'screening')->first();
        if (!$validFeeType) {
            $validFeeType = ( FeeType::where('is_active', true)->first() );
        }
        if (!$validFeeType) {
            $this->command->error("No valid fee type found. Please seed or create a fee type (e.g. 'screening') first.");
            return;
        }

        // Update all election offices that have a NULL fee_type_id
        $updated = ElectionOffice::whereNull('fee_type_id')->update(['fee_type_id' => $validFeeType->id]);
        $this->command->info("Updated {$updated} election offices (setting fee_type_id to {$validFeeType->id}).");
    }
} 