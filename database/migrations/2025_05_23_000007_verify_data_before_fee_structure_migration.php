<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create verification log table if it doesn't exist
        if (!Schema::hasTable('migration_verification_logs')) {
            Schema::create('migration_verification_logs', function (Blueprint $table) {
                $table->id();
                $table->string('migration_name');
                $table->json('verification_results');
                $table->boolean('passed');
                $table->timestamp('verified_at');
                $table->timestamps();
            });
        }

        $results = [];
        
        // Run all verification checks
        $this->verifyFeeTypes($results);
        $this->verifyFeeTemplates($results);
        $this->verifyFeeRules($results);
        $this->verifyActiveStatus($results);

        // Check if all verifications passed
        $allPassed = collect($results)->every(fn($result) => $result['passed']);

        // Log the verification results
        DB::table('migration_verification_logs')->insert([
            'migration_name' => 'verify_data_before_fee_structure_migration',
            'verification_results' => json_encode($results),
            'passed' => $allPassed,
            'verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // If any verification failed, throw an exception
        if (!$allPassed) {
            $failedChecks = collect($results)
                ->filter(fn($result) => !$result['passed'])
                ->map(fn($result) => $result['check_name'] . ': ' . $result['message'])
                ->join("\n");

            throw new \Exception("Verification failed:\n" . $failedChecks);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse verification
    }

    /**
     * Verify that all required fee types exist
     */
    private function verifyFeeTypes(array &$results): void
    {
        $requiredTypes = ['registration', 'development_levy', 'data_processing', 'office_contest'];
        $existingTypes = DB::table('fee_types')
            ->whereIn('code', $requiredTypes)
            ->pluck('code')
            ->toArray();

        $missingTypes = array_diff($requiredTypes, $existingTypes);
        
        $results[] = [
            'check_name' => 'Fee Types Verification',
            'message' => empty($missingTypes) 
                ? 'All required fee types exist'
                : 'Missing fee types: ' . implode(', ', $missingTypes),
            'passed' => empty($missingTypes),
            'details' => [
                'required_types' => $requiredTypes,
                'existing_types' => $existingTypes,
                'missing_types' => $missingTypes
            ]
        ];
    }

    /**
     * Verify fee templates data
     */
    private function verifyFeeTemplates(array &$results): void
    {
        // Check for templates without valid fee types
        $invalidFeeTypes = DB::table('fee_templates')
            ->leftJoin('fee_types', 'fee_templates.fee_type_id', '=', 'fee_types.id')
            ->whereNull('fee_types.id')
            ->count();

        // Check for templates with invalid graduation years
        $currentYear = date('Y');
        $invalidYears = DB::table('fee_templates')
            ->where('graduation_year', '<', $currentYear - 50)
            ->orWhere('graduation_year', '>', $currentYear + 5)
            ->count();

        $passed = ($invalidFeeTypes + $invalidYears) === 0;
        
        $results[] = [
            'check_name' => 'Fee Templates Verification',
            'message' => $passed 
                ? 'All fee templates are valid'
                : "Found invalid references: {$invalidFeeTypes} fee types, {$invalidYears} years",
            'passed' => $passed,
            'details' => [
                'invalid_fee_types' => $invalidFeeTypes,
                'invalid_years' => $invalidYears
            ]
        ];
    }

    /**
     * Verify fee rules data
     */
    private function verifyFeeRules(array &$results): void
    {
        // Check for rules without valid fee types
        $invalidFeeTypes = DB::table('fee_rules')
            ->leftJoin('fee_types', 'fee_rules.fee_type_id', '=', 'fee_types.id')
            ->whereNull('fee_types.id')
            ->count();

        // Check for invalid rule parameters
        $invalidParameters = DB::table('fee_rules')
            ->where(function($query) {
                $query->where('rule_type', 'graduation_year_range')
                    ->where(function($q) {
                        $q->whereRaw("JSON_VALID(rule_parameters) = 0")
                            ->orWhereRaw("JSON_EXTRACT(rule_parameters, '$.min_year') IS NULL")
                            ->orWhereRaw("JSON_EXTRACT(rule_parameters, '$.max_year') IS NULL");
                    });
            })
            ->orWhere(function($query) {
                $query->where('rule_type', 'office_type')
                    ->where(function($q) {
                        $q->whereRaw("JSON_VALID(rule_parameters) = 0")
                            ->orWhereRaw("JSON_EXTRACT(rule_parameters, '$.requires_application') IS NULL")
                            ->orWhereRaw("CAST(JSON_EXTRACT(rule_parameters, '$.requires_application') AS SIGNED) != 1");
                    });
            })
            ->count();

        // Log the actual rule parameters for debugging
        $rules = DB::table('fee_rules')
            ->where('rule_type', 'office_type')
            ->get(['id', 'rule_parameters']);
        
        foreach ($rules as $rule) {
            Log::info("Rule {$rule->id} parameters: " . $rule->rule_parameters);
        }

        $passed = ($invalidFeeTypes + $invalidParameters) === 0;
        
        $results[] = [
            'check_name' => 'Fee Rules Verification',
            'message' => $passed 
                ? 'All fee rules are valid'
                : "Found invalid references: {$invalidFeeTypes} fee types, {$invalidParameters} invalid parameters",
            'passed' => $passed,
            'details' => [
                'invalid_fee_types' => $invalidFeeTypes,
                'invalid_parameters' => $invalidParameters
            ]
        ];
    }

    /**
     * Verify active status consistency
     */
    private function verifyActiveStatus(array &$results): void
    {
        // Check for inactive fee types with active templates
        $inactiveTypesWithActiveTemplates = DB::table('fee_types')
            ->join('fee_templates', 'fee_types.id', '=', 'fee_templates.fee_type_id')
            ->where('fee_types.is_active', false)
            ->where('fee_templates.is_active', true)
            ->count();

        // Check for inactive fee types with active rules
        $inactiveTypesWithActiveRules = DB::table('fee_types')
            ->join('fee_rules', 'fee_types.id', '=', 'fee_rules.fee_type_id')
            ->where('fee_types.is_active', false)
            ->where('fee_rules.is_active', true)
            ->count();

        $passed = ($inactiveTypesWithActiveTemplates + $inactiveTypesWithActiveRules) === 0;
        
        $results[] = [
            'check_name' => 'Active Status Verification',
            'message' => $passed 
                ? 'All active statuses are consistent'
                : "Found {$inactiveTypesWithActiveTemplates} inactive types with active templates, {$inactiveTypesWithActiveRules} with active rules",
            'passed' => $passed,
            'details' => [
                'inactive_types_with_active_templates' => $inactiveTypesWithActiveTemplates,
                'inactive_types_with_active_rules' => $inactiveTypesWithActiveRules
            ]
        ];
    }
}; 