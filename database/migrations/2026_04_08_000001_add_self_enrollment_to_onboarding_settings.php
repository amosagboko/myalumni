<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onboarding_settings', function (Blueprint $table) {
            $table->boolean('is_self_enrollment_enabled')->default(false)->after('is_onboarding_enabled');
            $table->timestamp('self_enrollment_updated_at')->nullable()->after('reopened_by');
            $table->foreignId('self_enrollment_updated_by')->nullable()->after('self_enrollment_updated_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('onboarding_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('self_enrollment_updated_by');
            $table->dropColumn(['is_self_enrollment_enabled', 'self_enrollment_updated_at']);
        });
    }
};
