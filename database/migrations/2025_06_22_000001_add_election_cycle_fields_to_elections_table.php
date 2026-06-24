<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->unsignedSmallInteger('election_year')->nullable()->after('title');
            $table->string('cycle_label')->nullable()->after('election_year');
            $table->boolean('is_active')->default(false)->after('status');
            $table->timestamp('archived_at')->nullable()->after('is_active');
            $table->foreignId('archived_by')->nullable()->after('archived_at')->constrained('users')->nullOnDelete();
            $table->foreignId('cloned_from_election_id')->nullable()->after('archived_by')->constrained('elections')->nullOnDelete();
        });

        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('draft', 'eoi', 'accreditation', 'voting', 'completed', 'archived') DEFAULT 'draft'");

        $elections = DB::table('elections')->orderBy('id')->get();
        foreach ($elections as $election) {
            DB::table('elections')->where('id', $election->id)->update([
                'election_year' => (int) date('Y', strtotime($election->created_at)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            $table->dropForeign(['archived_by']);
            $table->dropForeign(['cloned_from_election_id']);
            $table->dropColumn([
                'election_year',
                'cycle_label',
                'is_active',
                'archived_at',
                'archived_by',
                'cloned_from_election_id',
            ]);
        });

        DB::statement("ALTER TABLE elections MODIFY COLUMN status ENUM('draft', 'eoi', 'accreditation', 'voting', 'completed') DEFAULT 'draft'");
    }
};
