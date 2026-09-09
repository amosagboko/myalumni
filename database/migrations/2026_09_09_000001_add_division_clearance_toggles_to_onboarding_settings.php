<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onboarding_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('onboarding_settings', 'is_student_affairs_clearance_enabled')) {
                $table->boolean('is_student_affairs_clearance_enabled')->default(true)->after('is_self_enrollment_enabled');
            }
            if (! Schema::hasColumn('onboarding_settings', 'is_academic_affairs_clearance_enabled')) {
                $table->boolean('is_academic_affairs_clearance_enabled')->default(true)->after('is_student_affairs_clearance_enabled');
            }
            if (! Schema::hasColumn('onboarding_settings', 'student_affairs_clearance_updated_at')) {
                $table->timestamp('student_affairs_clearance_updated_at')->nullable()->after('is_academic_affairs_clearance_enabled');
            }
            if (! Schema::hasColumn('onboarding_settings', 'student_affairs_clearance_updated_by')) {
                $table->unsignedBigInteger('student_affairs_clearance_updated_by')->nullable()->after('student_affairs_clearance_updated_at');
            }
            if (! Schema::hasColumn('onboarding_settings', 'academic_affairs_clearance_updated_at')) {
                $table->timestamp('academic_affairs_clearance_updated_at')->nullable()->after('student_affairs_clearance_updated_by');
            }
            if (! Schema::hasColumn('onboarding_settings', 'academic_affairs_clearance_updated_by')) {
                $table->unsignedBigInteger('academic_affairs_clearance_updated_by')->nullable()->after('academic_affairs_clearance_updated_at');
            }
        });

        // Drop auto-generated long names from a partial previous run (MySQL max 64 chars).
        $this->dropForeignIfExists('onboarding_settings_student_affairs_clearance_updated_by_foreign');
        $this->dropForeignIfExists('onboarding_settings_academic_affairs_clearance_updated_by_foreign');

        if (! $this->foreignExists('os_sa_clearance_updated_by_fk')) {
            Schema::table('onboarding_settings', function (Blueprint $table) {
                $table->foreign('student_affairs_clearance_updated_by', 'os_sa_clearance_updated_by_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }

        if (! $this->foreignExists('os_aa_clearance_updated_by_fk')) {
            Schema::table('onboarding_settings', function (Blueprint $table) {
                $table->foreign('academic_affairs_clearance_updated_by', 'os_aa_clearance_updated_by_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $this->dropForeignIfExists('os_aa_clearance_updated_by_fk');
        $this->dropForeignIfExists('os_sa_clearance_updated_by_fk');

        Schema::table('onboarding_settings', function (Blueprint $table) {
            foreach ([
                'academic_affairs_clearance_updated_by',
                'academic_affairs_clearance_updated_at',
                'student_affairs_clearance_updated_by',
                'student_affairs_clearance_updated_at',
                'is_academic_affairs_clearance_enabled',
                'is_student_affairs_clearance_enabled',
            ] as $column) {
                if (Schema::hasColumn('onboarding_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function foreignExists(string $constraintName): bool
    {
        $database = DB::getDatabaseName();

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', $database)
            ->where('TABLE_NAME', 'onboarding_settings')
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    private function dropForeignIfExists(string $constraintName): void
    {
        if (! $this->foreignExists($constraintName)) {
            return;
        }

        Schema::table('onboarding_settings', function (Blueprint $table) use ($constraintName) {
            $table->dropForeign($constraintName);
        });
    }
};
