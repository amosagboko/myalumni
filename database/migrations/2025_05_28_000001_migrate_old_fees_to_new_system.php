<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\FeeTemplate;
use App\Models\CategoryTransactionFee;
use App\Models\Transaction;
use App\Models\AlumniYear;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, create fee templates for all active category transaction fees
        $oldFees = CategoryTransactionFee::where('is_active', true)->get();
        
        foreach ($oldFees as $oldFee) {
            // Create a new fee template
            $feeTemplate = FeeTemplate::create([
                'fee_type_id' => $oldFee->fee_type_id,
                'category_id' => $oldFee->category_id,
                'graduation_year' => $oldFee->alumniYear->year,
                'amount' => $oldFee->amount,
                'description' => $oldFee->description,
                'is_active' => true,
                'valid_from' => now(),
                'valid_until' => null,
                'is_test_mode' => $oldFee->is_test_mode,
                'old_fee_id' => $oldFee->id // Keep reference to old fee for migration
            ]);

            // Update transactions to use new fee template
            Transaction::where('category_transaction_fee_id', $oldFee->id)
                ->update([
                    'fee_template_id' => $feeTemplate->id,
                    'category_transaction_fee_id' => null
                ]);
        }

        // Add fee_template_id column if it doesn't exist
        if (!Schema::hasColumn('transactions', 'fee_template_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->foreignId('fee_template_id')->nullable()->after('alumni_id')
                    ->references('id')->on('fee_templates')->onDelete('set null');
            });
        }

        // Drop the old foreign key and column
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['category_transaction_fee_id']);
            $table->dropColumn('category_transaction_fee_id');
        });

        // Drop the old fees table
        Schema::dropIfExists('category_transaction_fees');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the old fees table
        Schema::create('category_transaction_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('alumni_categories')->onDelete('cascade');
            $table->foreignId('fee_type_id')->constrained('fee_types')->onDelete('cascade');
            $table->foreignId('alumni_year_id')->constrained('alumni_years')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_test_mode')->default(false);
            $table->timestamps();

            $table->unique(['category_id', 'fee_type_id', 'alumni_year_id']);
        });

        // Add back the old column
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('category_transaction_fee_id')->nullable()->after('alumni_id')
                ->references('id')->on('category_transaction_fees')->onDelete('set null');
        });

        // Migrate data back
        $feeTemplates = FeeTemplate::whereNotNull('old_fee_id')->get();
        
        foreach ($feeTemplates as $template) {
            // Recreate old fee
            $oldFee = CategoryTransactionFee::create([
                'category_id' => $template->category_id,
                'fee_type_id' => $template->fee_type_id,
                'alumni_year_id' => AlumniYear::where('year', $template->graduation_year)->first()->id,
                'amount' => $template->amount,
                'description' => $template->description,
                'is_active' => $template->is_active,
                'is_test_mode' => $template->is_test_mode
            ]);

            // Update transactions back to old fee
            Transaction::where('fee_template_id', $template->id)
                ->update([
                    'category_transaction_fee_id' => $oldFee->id,
                    'fee_template_id' => null
                ]);
        }

        // Drop the new column
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['fee_template_id']);
            $table->dropColumn('fee_template_id');
        });
    }
}; 