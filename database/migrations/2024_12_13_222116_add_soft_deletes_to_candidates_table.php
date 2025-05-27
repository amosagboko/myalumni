<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('candidates')) {
            Schema::table('candidates', function (Blueprint $table) {
                if (!Schema::hasColumn('candidates', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('candidates')) {
            Schema::table('candidates', function (Blueprint $table) {
                if (Schema::hasColumn('candidates', 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }
}; 