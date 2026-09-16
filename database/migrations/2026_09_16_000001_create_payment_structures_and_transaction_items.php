<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_structures')) {
            Schema::create('payment_structures', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('display_title')->nullable();
                $table->string('payment_mode', 32)->default('separate');
                $table->unsignedInteger('graduation_year')->nullable();
                $table->foreignId('category_id')->nullable()->constrained('alumni_categories')->nullOnDelete();
                $table->string('credo_service_code_key', 64)->default('combined');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['payment_mode', 'is_active']);
                $table->index('graduation_year');
            });
        }

        if (! Schema::hasTable('payment_structure_items')) {
            Schema::create('payment_structure_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_structure_id')->constrained('payment_structures')->cascadeOnDelete();
                $table->foreignId('fee_template_id')->constrained('fee_templates')->restrictOnDelete();
                $table->timestamps();

                $table->unique(['payment_structure_id', 'fee_template_id'], 'payment_structure_items_unique');
            });
        }

        if (! Schema::hasTable('transaction_items')) {
            Schema::create('transaction_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
                $table->foreignId('fee_template_id')->constrained('fee_templates')->restrictOnDelete();
                $table->foreignId('fee_type_id')->nullable()->constrained('fee_types')->nullOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('alumni_categories')->nullOnDelete();
                $table->string('description')->nullable();
                $table->decimal('amount', 10, 2);
                $table->timestamps();

                $table->unique(['transaction_id', 'fee_template_id'], 'transaction_items_unique');
                $table->index(['fee_template_id']);
            });
        }

        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'fee_template_id')) {
            $this->dropFeeTemplateForeignKey();
            DB::statement('ALTER TABLE transactions MODIFY fee_template_id BIGINT UNSIGNED NULL');

            $existing = collect(DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'transactions'
                  AND COLUMN_NAME = 'fee_template_id'
                  AND REFERENCED_TABLE_NAME = 'fee_templates'
            "))->isNotEmpty();

            if (! $existing) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->foreign('fee_template_id')
                        ->references('id')
                        ->on('fee_templates')
                        ->restrictOnDelete();
                });
            }
        }

        if (Schema::hasTable('transactions') && ! Schema::hasColumn('transactions', 'payment_structure_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('payment_structure_id')
                    ->nullable()
                    ->after('fee_template_id')
                    ->constrained('payment_structures')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'payment_structure_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign(['payment_structure_id']);
                $table->dropColumn('payment_structure_id');
            });
        }

        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'fee_template_id')) {
            $this->dropFeeTemplateForeignKey();
            DB::statement('ALTER TABLE transactions MODIFY fee_template_id BIGINT UNSIGNED NOT NULL');
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreign('fee_template_id')
                    ->references('id')
                    ->on('fee_templates')
                    ->restrictOnDelete();
            });
        }

        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('payment_structure_items');
        Schema::dropIfExists('payment_structures');
    }

    protected function dropFeeTemplateForeignKey(): void
    {
        $keys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'transactions'
              AND COLUMN_NAME = 'fee_template_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($keys as $key) {
            DB::statement('ALTER TABLE transactions DROP FOREIGN KEY `'.$key->CONSTRAINT_NAME.'`');
        }
    }
};
