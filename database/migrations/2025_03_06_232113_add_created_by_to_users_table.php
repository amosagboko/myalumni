<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Add the created_by column (nullable to avoid affecting existing data)
            $table->unsignedBigInteger('created_by')->nullable()->after('id');

            // Create a foreign key constraint (optional but recommended)
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the foreign key and column if rolling back
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
