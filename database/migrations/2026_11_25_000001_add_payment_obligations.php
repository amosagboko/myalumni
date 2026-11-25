<?php

use App\Services\PaymentObligationService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('obligation_id')->nullable()->after('alumni_id');
            $table->boolean('is_eoi')->default(false)->after('status');
        });

        $obligations = app(PaymentObligationService::class);
        $obligations->linkDuplicateObligations();

        DB::statement("
            ALTER TABLE transactions
            ADD open_obligation_key VARCHAR(80)
            GENERATED ALWAYS AS (
                CASE
                    WHEN obligation_id IS NOT NULL OR status = 'paid' OR is_eoi = 1 THEN NULL
                    WHEN fee_template_id IS NOT NULL THEN CONCAT('fee:', alumni_id, ':', fee_template_id)
                    WHEN payment_structure_id IS NOT NULL THEN CONCAT('combined:', alumni_id, ':', payment_structure_id)
                    ELSE NULL
                END
            ) VIRTUAL
        ");

        DB::statement('CREATE UNIQUE INDEX transactions_open_obligation_key_unique ON transactions (open_obligation_key)');

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('obligation_id')->references('id')->on('transactions')->nullOnDelete();
        });

        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->restrictOnDelete();
            $table->string('payment_reference', 100)->unique();
            $table->string('payment_provider_reference', 100)->nullable()->index();
            $table->text('payment_link')->nullable();
            $table->string('status', 20)->default('pending');
            $table->decimal('amount', 10, 2);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index('transaction_id');
        });

        $obligations->recordAttemptsForLinkedRows();
    }

    public function down(): void
    {
        DB::statement('DROP INDEX transactions_open_obligation_key_unique ON transactions');
        DB::statement('ALTER TABLE transactions DROP COLUMN open_obligation_key');

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('obligation_id');
            $table->dropColumn('is_eoi');
        });

        Schema::dropIfExists('payment_attempts');
    }
};
