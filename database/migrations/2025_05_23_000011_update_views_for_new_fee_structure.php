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

        // Create view for fee templates
        DB::statement("
            CREATE OR REPLACE VIEW vw_fee_templates AS
            SELECT 
                ft.id,
                ft.fee_type_id,
                ft.graduation_year,
                ft.amount,
                ft.description,
                ft.is_active,
                ft.valid_from,
                ft.valid_until,
                ft.created_at,
                ft.updated_at,
                fty.code as fee_type_code,
                fty.name as fee_type_name,
                fty.description as fee_type_description,
                COUNT(t.id) as transaction_count
            FROM fee_templates ft
            LEFT JOIN fee_types fty ON ft.fee_type_id = fty.id
            LEFT JOIN transactions t ON ft.id = t.fee_template_id
            GROUP BY ft.id, ft.fee_type_id, ft.graduation_year, ft.amount, 
                     ft.description, ft.is_active, ft.valid_from, ft.valid_until,
                     ft.created_at, ft.updated_at, fty.code, fty.name, fty.description
        ");

        // Create view for fee types
        DB::statement("
            CREATE OR REPLACE VIEW vw_fee_types AS
            SELECT 
                ft.id,
                ft.name,
                ft.code,
                ft.description,
                ft.is_active,
                ft.is_system,
                ft.created_at,
                ft.updated_at,
                COUNT(DISTINCT vft.id) as template_count,
                SUM(CASE WHEN vft.is_active = 1 THEN 1 ELSE 0 END) as active_template_count
            FROM fee_types ft
            LEFT JOIN vw_fee_templates vft ON ft.id = vft.fee_type_id
            GROUP BY ft.id, ft.name, ft.code, ft.description, ft.is_active, ft.is_system, ft.created_at, ft.updated_at
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS vw_transactions');
        DB::statement('DROP VIEW IF EXISTS vw_fee_templates');
        DB::statement('DROP VIEW IF EXISTS vw_fee_types');
    }
}; 