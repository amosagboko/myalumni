<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW vw_transactions AS
            SELECT 
                t.id,
                t.alumni_id,
                a.user_id,
                t.fee_template_id,
                t.amount,
                t.status,
                t.is_test_mode,
                t.payment_reference,
                t.payment_method,
                t.payment_provider,
                t.payment_provider_reference,
                t.payment_link,
                t.payment_details,
                t.failure_reason,
                t.paid_at,
                t.failed_at,
                t.created_at,
                t.updated_at,
                t.deleted_at,
                ft.id as template_id,
                ft.fee_type_id,
                ft.graduation_year,
                ft.amount as fee_amount,
                ft.is_active as fee_is_active,
                ft.valid_from as fee_valid_from,
                ft.valid_until as fee_valid_until,
                ft.description as fee_description,
                fty.code as fee_type_code,
                fty.name as fee_type_name,
                fty.description as fee_type_description,
                a.matric_number,
                a.programme,
                a.department,
                a.faculty,
                a.year_of_graduation,
                a.year_of_entry
            FROM transactions t
            LEFT JOIN fee_templates ft ON t.fee_template_id = ft.id
            LEFT JOIN fee_types fty ON ft.fee_type_id = fty.id
            LEFT JOIN alumni a ON t.alumni_id = a.id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to do anything in down() since we're just updating a view
    }
}; 