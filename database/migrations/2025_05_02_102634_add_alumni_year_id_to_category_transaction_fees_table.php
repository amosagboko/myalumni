<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the foreign key (or column) exists before dropping it
        if (Schema::hasColumn('category_transaction_fees', 'category_id')) {
            try {
                Schema::table('category_transaction_fees', function (Blueprint $table) {
                    $table->dropForeign(['category_id']);
                });
            } catch (\Exception $e) {
                // Foreign key (or column) does not exist, continue
            }
        }

        // Check if the index (or constraint) exists before dropping it
        if (Schema::hasIndex('category_transaction_fees', 'category_transaction_fees_category_id_fee_type_year_unique')) {
            try {
                Schema::table('category_transaction_fees', function (Blueprint $table) {
                    $table->dropUnique('category_transaction_fees_category_id_fee_type_year_unique');
                });
            } catch (\Exception $e) {
                // Index (or constraint) does not exist, continue
            }
        }

        // Then add the new column (and its FK constraint) and (re-)add the unique index (or constraint)
        Schema::table('category_transaction_fees', function (Blueprint $table) {
            $table->foreignId('alumni_year_id')->after('category_id')->constrained('alumni_years')->onDelete('cascade');
            $table->unique(['category_id', 'fee_type', 'alumni_year_id'], 'category_transaction_fees_category_id_fee_type_year_unique');
        });

        // (Re-)add the foreign key constraint (if needed)
        if (Schema::hasColumn('category_transaction_fees', 'category_id')) {
            Schema::table('category_transaction_fees', function (Blueprint $table) {
                $table->foreign('category_id')->references('id')->on('alumni_categories')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if the foreign key (or column) exists before dropping it
        if (Schema::hasColumn('category_transaction_fees', 'category_id')) {
            try {
                Schema::table('category_transaction_fees', function (Blueprint $table) {
                    $table->dropForeign(['category_id']);
                });
            } catch (\Exception $e) {
                // Foreign key (or column) does not exist, continue
            }
        }

        // Check if the index (or constraint) exists before dropping it
        if (Schema::hasIndex('category_transaction_fees', 'category_transaction_fees_category_id_fee_type_year_unique')) {
            try {
                Schema::table('category_transaction_fees', function (Blueprint $table) {
                    $table->dropUnique('category_transaction_fees_category_id_fee_type_year_unique');
                });
            } catch (\Exception $e) {
                // Index (or constraint) does not exist, continue
            }
        }

        // Drop the alumni_year_id column (and its FK constraint) (if it exists)
        if (Schema::hasColumn('category_transaction_fees', 'alumni_year_id')) {
            Schema::table('category_transaction_fees', function (Blueprint $table) {
                $table->dropForeign(['alumni_year_id']);
                $table->dropColumn('alumni_year_id');
            });
        }

        // (Re-)add the foreign key constraint (if needed)
        if (Schema::hasColumn('category_transaction_fees', 'category_id')) {
            Schema::table('category_transaction_fees', function (Blueprint $table) {
                $table->foreign('category_id')->references('id')->on('alumni_categories')->onDelete('cascade');
            });
        }
    }
};
