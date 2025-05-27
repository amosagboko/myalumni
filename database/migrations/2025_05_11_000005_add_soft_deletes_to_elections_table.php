<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First, drop foreign key constraints if they exist
        if (Schema::hasTable('election_offices')) {
            Schema::table('election_offices', function (Blueprint $table) {
                if (Schema::hasColumn('election_offices', 'election_id')) {
                    $table->dropForeign(['election_id']);
                }
            });
        }
        
        if (Schema::hasTable('candidates')) {
            Schema::table('candidates', function (Blueprint $table) {
                if (Schema::hasColumn('candidates', 'election_id')) {
                    $table->dropForeign(['election_id']);
                }
            });
        }
        
        if (Schema::hasTable('accredited_voters')) {
            Schema::table('accredited_voters', function (Blueprint $table) {
                if (Schema::hasColumn('accredited_voters', 'election_id')) {
                    $table->dropForeign(['election_id']);
                }
            });
        }
        
        if (Schema::hasTable('votes')) {
            Schema::table('votes', function (Blueprint $table) {
                if (Schema::hasColumn('votes', 'election_id')) {
                    $table->dropForeign(['election_id']);
                }
            });
        }
        
        if (Schema::hasTable('election_results')) {
            Schema::table('election_results', function (Blueprint $table) {
                if (Schema::hasColumn('election_results', 'election_id')) {
                    $table->dropForeign(['election_id']);
                }
            });
        }

        // Add soft deletes column
        Schema::table('elections', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('elections', function (Blueprint $table) {
            if (Schema::hasColumn('elections', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        // Re-add foreign key constraints if tables exist
        if (Schema::hasTable('election_offices')) {
            Schema::table('election_offices', function (Blueprint $table) {
                if (Schema::hasColumn('election_offices', 'election_id')) {
                    $table->foreign('election_id')->references('id')->on('elections')->onDelete('cascade');
                }
            });
        }
        
        if (Schema::hasTable('candidates')) {
            Schema::table('candidates', function (Blueprint $table) {
                if (Schema::hasColumn('candidates', 'election_id')) {
                    $table->foreign('election_id')->references('id')->on('elections')->onDelete('cascade');
                }
            });
        }
        
        if (Schema::hasTable('accredited_voters')) {
            Schema::table('accredited_voters', function (Blueprint $table) {
                if (Schema::hasColumn('accredited_voters', 'election_id')) {
                    $table->foreign('election_id')->references('id')->on('elections')->onDelete('cascade');
                }
            });
        }
        
        if (Schema::hasTable('votes')) {
            Schema::table('votes', function (Blueprint $table) {
                if (Schema::hasColumn('votes', 'election_id')) {
                    $table->foreign('election_id')->references('id')->on('elections')->onDelete('cascade');
                }
            });
        }
        
        if (Schema::hasTable('election_results')) {
            Schema::table('election_results', function (Blueprint $table) {
                if (Schema::hasColumn('election_results', 'election_id')) {
                    $table->foreign('election_id')->references('id')->on('elections')->onDelete('cascade');
                }
            });
        }
    }
}; 