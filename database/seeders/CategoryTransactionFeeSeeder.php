<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AlumniCategory;
use App\Models\CategoryTransactionFee;
use App\Models\AlumniYear;
use App\Models\FeeType;

class CategoryTransactionFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = AlumniCategory::all();
        $alumniYears = AlumniYear::where('is_active', true)->get();
        $feeTypes = FeeType::where('is_active', true)->get();

        // Default fees for each category
        $defaultFees = [
            'Postgraduate' => [
                'registration' => 5000.00,
                'development_levy' => 10000.00,
                'data_processing' => 2000.00,
            ],
            'Undergraduate (Full-time)' => [
                'registration' => 3000.00,
                'development_levy' => 5000.00,
                'data_processing' => 1500.00,
            ],
            'Undergraduate (Part-time)' => [
                'registration' => 4000.00,
                'development_levy' => 7500.00,
                'data_processing' => 1500.00,
            ],
            'Diploma' => [
                'registration' => 2500.00,
                'development_levy' => 3000.00,
                'data_processing' => 1000.00,
            ],
            'Alumni Annual Registration (Subscription)' => [
                'subscription' => 2000.00,
            ],
        ];

        foreach ($categories as $category) {
            if (isset($defaultFees[$category->name])) {
                foreach ($alumniYears as $year) {
                    foreach ($defaultFees[$category->name] as $feeTypeCode => $amount) {
                        $feeType = $feeTypes->firstWhere('code', $feeTypeCode);
                        if ($feeType) {
                            CategoryTransactionFee::firstOrCreate(
                                [
                                    'category_id' => $category->id,
                                    'alumni_year_id' => $year->id,
                                    'fee_type_id' => $feeType->id,
                                ],
                                [
                                    'amount' => $amount,
                                    'description' => $feeType->name . ' for ' . $category->name . ' (' . $year->year . ')',
                                    'is_active' => true,
                                ]
                            );
                        }
                    }
                }
            }
        }
    }
}
