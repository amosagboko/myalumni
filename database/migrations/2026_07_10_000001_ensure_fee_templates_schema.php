<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('fee_templates')) {
            return;
        }

        Schema::table('fee_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('fee_templates', 'category_id') && Schema::hasTable('alumni_categories')) {
                $table->foreignId('category_id')->nullable()->after('fee_type_id')
                    ->constrained('alumni_categories')->nullOnDelete();
            }

            if (! Schema::hasColumn('fee_templates', 'name')) {
                $table->string('name', 255)->nullable()->after('fee_type_id');
            }

            if (! Schema::hasColumn('fee_templates', 'fee_purpose')) {
                $table->string('fee_purpose', 32)->nullable()->after('fee_type_id');
                $table->index('fee_purpose');
            }
        });

        if (Schema::hasColumn('fee_templates', 'category_id')) {
            $hasOld = collect(DB::select("SHOW INDEX FROM fee_templates WHERE Key_name = 'unique_fee_type_year_valid_from'"))->isNotEmpty();
            $hasNew = collect(DB::select("SHOW INDEX FROM fee_templates WHERE Key_name = 'unique_fee_type_category_year_valid_from'"))->isNotEmpty();

            if ($hasOld && ! $hasNew) {
                Schema::table('fee_templates', function (Blueprint $table) {
                    $table->dropUnique('unique_fee_type_year_valid_from');
                    $table->unique(
                        ['fee_type_id', 'category_id', 'graduation_year', 'valid_from'],
                        'unique_fee_type_category_year_valid_from'
                    );
                });
            }
        }

        if (Schema::hasTable('fee_types') && ! DB::table('fee_types')->where('code', 'tech_support')->exists()) {
            DB::table('fee_types')->insert([
                'code' => 'tech_support',
                'name' => 'Tech Support Fee',
                'description' => 'Technology support and maintenance fee for alumni portal',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Intentionally left empty — this migration consolidates forward-only fixes.
    }
};
