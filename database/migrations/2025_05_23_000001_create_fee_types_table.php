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
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default fee types
        DB::table('fee_types')->insert([
            [
                'code' => 'registration',
                'name' => 'Registration Fee',
                'description' => 'Annual registration fee for alumni membership',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'development_levy',
                'name' => 'Development Levy',
                'description' => 'Annual development levy for alumni projects',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'eoi',
                'name' => 'Expression of Interest',
                'description' => 'Fee for expressing interest in contesting for office',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'office_contest',
                'name' => 'Office Contest Fee',
                'description' => 'Fee for contesting for an office position',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'code' => 'data_processing',
                'name' => 'Data Processing Fee',
                'description' => 'Fee for processing alumni data and updates',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_types');
    }
}; 