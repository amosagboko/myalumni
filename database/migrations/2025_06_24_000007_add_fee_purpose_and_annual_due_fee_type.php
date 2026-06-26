<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_templates', function (Blueprint $table) {
            $table->string('fee_purpose', 32)->nullable()->after('fee_type_id');
            $table->index('fee_purpose');
        });

        $now = now();

        if (!DB::table('fee_types')->where('code', 'annual_due')->exists()) {
            DB::table('fee_types')->insert([
                'code' => 'annual_due',
                'name' => 'Annual Alumni Due',
                'description' => 'Yearly renewal due after onboarding (one per payment year)',
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $onboardingCodes = ['registration', 'development_levy', 'data_processing', 'tech_support'];
        $onboardingTypeIds = DB::table('fee_types')->whereIn('code', $onboardingCodes)->pluck('id');

        DB::table('fee_templates')
            ->whereIn('fee_type_id', $onboardingTypeIds)
            ->whereNull('fee_purpose')
            ->update(['fee_purpose' => 'onboarding']);

        $subscriptionTypeId = DB::table('fee_types')->where('code', 'subscription')->value('id');
        if ($subscriptionTypeId) {
            DB::table('fee_templates')
                ->where('fee_type_id', $subscriptionTypeId)
                ->whereNull('fee_purpose')
                ->update(['fee_purpose' => 'annual_renewal']);
        }
    }

    public function down(): void
    {
        DB::table('fee_types')->where('code', 'annual_due')->delete();

        Schema::table('fee_templates', function (Blueprint $table) {
            $table->dropIndex(['fee_purpose']);
            $table->dropColumn('fee_purpose');
        });
    }
};
