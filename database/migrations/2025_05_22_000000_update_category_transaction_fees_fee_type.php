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
        // Check if fee_type_id column already exists
        if (!Schema::hasColumn('category_transaction_fees', 'fee_type_id')) {
            // First, add the new fee_type_id column
            Schema::table('category_transaction_fees', function (Blueprint $table) {
                $table->foreignId('fee_type_id')->nullable()->after('fee_type')->constrained('fee_types');
            });
        }

        // Only proceed with data migration if fee_type column still exists
        if (Schema::hasColumn('category_transaction_fees', 'fee_type')) {
            // Migrate the data from fee_type enum to fee_type_id
            $feeTypes = [
                'registration' => 'registration',
                'development_levy' => 'development_levy',
                'data_processing' => 'data_processing'
            ];

            foreach ($feeTypes as $oldType => $code) {
                // Find or create the fee type
                $feeType = DB::table('fee_types')
                    ->where('code', $code)
                    ->first();

                if (!$feeType) {
                    $feeTypeId = DB::table('fee_types')->insertGetId([
                        'name' => ucwords(str_replace('_', ' ', $code)),
                        'code' => $code,
                        'description' => 'Fee for ' . str_replace('_', ' ', $code),
                        'is_active' => true,
                        'is_system' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    $feeTypeId = $feeType->id;
                }

                // Update the records only if fee_type_id is null
                DB::table('category_transaction_fees')
                    ->where('fee_type', $oldType)
                    ->whereNull('fee_type_id')
                    ->update(['fee_type_id' => $feeTypeId]);
            }

            // Make fee_type_id required if it's not already
            if (Schema::getConnection()->getDoctrineSchemaManager()
                ->listTableDetails('category_transaction_fees')
                ->getColumn('fee_type_id')
                ->getNotnull() === false) {
                Schema::table('category_transaction_fees', function (Blueprint $table) {
                    $table->foreignId('fee_type_id')->nullable(false)->change();
                });
            }

            // Drop any existing unique constraints that might include fee_type
            $constraints = [
                'category_transaction_fees_category_id_fee_type_year_unique',
                'category_transaction_fees_category_id_fee_type_alumni_year_id_unique',
                'category_transaction_fees_category_fee_type_year_unique'
            ];

            foreach ($constraints as $constraint) {
                if ($this->constraintExists('category_transaction_fees', $constraint)) {
                    Schema::table('category_transaction_fees', function (Blueprint $table) use ($constraint) {
                        $table->dropUnique($constraint);
                    });
                }
            }

            // Drop the old fee_type column
            Schema::table('category_transaction_fees', function (Blueprint $table) {
                $table->dropColumn('fee_type');
            });
        }

        // Add new unique constraint if it doesn't exist
        if (!$this->constraintExists('category_transaction_fees', 'category_transaction_fees_category_fee_type_year_unique')) {
            Schema::table('category_transaction_fees', function (Blueprint $table) {
                $table->unique(['category_id', 'fee_type_id', 'alumni_year_id'], 'category_transaction_fees_category_fee_type_year_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new unique constraint if it exists
        if ($this->constraintExists('category_transaction_fees', 'category_transaction_fees_category_fee_type_year_unique')) {
            Schema::table('category_transaction_fees', function (Blueprint $table) {
                $table->dropUnique('category_transaction_fees_category_fee_type_year_unique');
            });
        }

        // Add back the fee_type column
        Schema::table('category_transaction_fees', function (Blueprint $table) {
            $table->enum('fee_type', ['registration', 'development_levy', 'data_processing'])->after('fee_type_id');
        });

        // Migrate data back
        $feeTypes = DB::table('fee_types')->get();
        foreach ($feeTypes as $feeType) {
            DB::table('category_transaction_fees')
                ->where('fee_type_id', $feeType->id)
                ->update(['fee_type' => $feeType->code]);
        }

        // Drop the fee_type_id column and its foreign key
        Schema::table('category_transaction_fees', function (Blueprint $table) {
            $table->dropForeign(['fee_type_id']);
            $table->dropColumn('fee_type_id');
        });

        // Add back the old unique constraint
        Schema::table('category_transaction_fees', function (Blueprint $table) {
            $table->unique(['category_id', 'fee_type', 'alumni_year_id'], 'category_transaction_fees_category_id_fee_type_year_unique');
        });
    }

    /**
     * Check if a constraint exists on a table
     */
    private function constraintExists(string $table, string $constraint): bool
    {
        $conn = Schema::getConnection()->getDoctrineSchemaManager();
        $doctrineTable = $conn->listTableDetails($table);
        return $doctrineTable->hasIndex($constraint);
    }
}; 