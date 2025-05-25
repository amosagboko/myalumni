<?php

namespace Database\Seeders;

use App\Models\AlumniCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AlumniCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Postgraduate',
                'slug' => 'postgraduate',
                'description' => 'Alumni who completed postgraduate programs (Masters, PhD, etc.)',
                'is_active' => true
            ],
            [
                'name' => 'Undergraduate (Full-time)',
                'slug' => 'undergraduate-full-time',
                'description' => 'Alumni who completed undergraduate programs on a full-time basis',
                'is_active' => true
            ],
            [
                'name' => 'Undergraduate (Part-time)',
                'slug' => 'undergraduate-part-time',
                'description' => 'Alumni who completed undergraduate programs on a part-time basis',
                'is_active' => true
            ],
            [
                'name' => 'Diploma',
                'slug' => 'diploma',
                'description' => 'Alumni who completed diploma programs',
                'is_active' => true
            ],
            [
                'name' => 'Alumni Annual Registration (Subscription)',
                'slug' => 'alumni-annual-registration',
                'description' => 'Annual registration subscription for all alumni',
                'is_active' => true
            ]
        ];

        foreach ($categories as $category) {
            AlumniCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
