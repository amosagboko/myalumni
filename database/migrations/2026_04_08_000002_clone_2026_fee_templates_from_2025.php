<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $sourceYear = 2025;
        $targetYear = 2026;

        $templates = DB::table('fee_templates')
            ->where('graduation_year', $sourceYear)
            ->where(function ($query) {
                $query->where('fee_purpose', 'onboarding')
                    ->orWhereNull('fee_purpose')
                    ->orWhere('fee_purpose', '');
            })
            ->get();

        // Also clone subscription templates tied to 2025 if present
        $subscriptionTemplates = DB::table('fee_templates')
            ->where('graduation_year', $sourceYear)
            ->where('fee_purpose', 'subscription')
            ->get();

        $all = $templates->merge($subscriptionTemplates)->unique('id');

        foreach ($all as $template) {
            $exists = DB::table('fee_templates')
                ->where('graduation_year', $targetYear)
                ->where('fee_type_id', $template->fee_type_id)
                ->where(function ($query) use ($template) {
                    if ($template->category_id === null) {
                        $query->whereNull('category_id');
                    } else {
                        $query->where('category_id', $template->category_id);
                    }
                })
                ->where('fee_purpose', $template->fee_purpose)
                ->exists();

            if ($exists) {
                continue;
            }

            $name = (string) ($template->name ?? '');
            $name = str_replace((string) $sourceYear, (string) $targetYear, $name);

            $description = (string) ($template->description ?? '');
            $description = str_replace((string) $sourceYear, (string) $targetYear, $description);
            if ($description === (string) ($template->description ?? '') && $description !== '') {
                $description .= " ({$targetYear})";
            }

            DB::table('fee_templates')->insert([
                'fee_type_id' => $template->fee_type_id,
                'category_id' => $template->category_id,
                'name' => $name !== '' ? $name : null,
                'graduation_year' => $targetYear,
                'amount' => $template->amount,
                'description' => $description ?: "Fee template for {$targetYear}",
                'is_active' => $template->is_active ?? true,
                'valid_from' => $targetYear.'-01-01',
                'valid_until' => $targetYear.'-12-31',
                'fee_purpose' => $template->fee_purpose,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ensure an AlumniYear row exists for 2026 payment/cohort tooling when the table is present
        if (Schema::hasTable('alumni_years')) {
            $yearExists = DB::table('alumni_years')->where('year', $targetYear)->exists();
            if (! $yearExists) {
                $columns = Schema::getColumnListing('alumni_years');
                $row = ['year' => $targetYear, 'created_at' => now(), 'updated_at' => now()];

                if (in_array('is_active', $columns, true)) {
                    $row['is_active'] = false;
                }
                if (in_array('start_date', $columns, true)) {
                    $row['start_date'] = $targetYear.'-01-01';
                }
                if (in_array('end_date', $columns, true)) {
                    $row['end_date'] = $targetYear.'-12-31';
                }
                if (in_array('label', $columns, true)) {
                    $row['label'] = (string) $targetYear;
                }
                if (in_array('name', $columns, true)) {
                    $row['name'] = (string) $targetYear;
                }

                DB::table('alumni_years')->insert($row);
            }
        }
    }

    public function down(): void
    {
        DB::table('fee_templates')->where('graduation_year', 2026)->delete();
    }
};
