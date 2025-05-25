<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->string('title')->nullable()->after('gender');
            $table->string('nationality')->nullable()->after('title');
            $table->text('contact_address')->nullable()->after('nationality');
            $table->string('phone_number')->nullable()->after('contact_address');
            $table->string('qualification_type')->nullable()->after('phone_number');
            $table->longText('qualification_details')->nullable()->after('qualification_type');
            $table->string('present_employer')->nullable()->after('qualification_details');
            $table->string('present_designation')->nullable()->after('present_employer');
            $table->text('professional_bodies')->nullable()->after('present_designation');
            $table->text('student_responsibilities')->nullable()->after('professional_bodies');
            $table->text('hobbies')->nullable()->after('student_responsibilities');
            $table->longText('other_information')->nullable()->after('hobbies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'nationality',
                'contact_address',
                'phone_number',
                'qualification_type',
                'qualification_details',
                'present_employer',
                'present_designation',
                'professional_bodies',
                'student_responsibilities',
                'hobbies',
                'other_information'
            ]);
        });
    }
};
