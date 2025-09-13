<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('contracts', 'version')) {
                $table->unsignedInteger('version')->default(1)->after('cancellation_reason');
            }
            if (!Schema::hasColumn('contracts', 'edited_by')) {
                $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete()->after('version');
            }
            if (!Schema::hasColumn('contracts', 'edited_at')) {
                $table->timestamp('edited_at')->nullable()->after('edited_by');
            }
            if (!Schema::hasColumn('contracts', 're_sent_at')) {
                $table->timestamp('re_sent_at')->nullable()->after('edited_at');
            }
            if (!Schema::hasColumn('contracts', 'revision_note')) {
                $table->string('revision_note', 500)->nullable()->after('re_sent_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (Schema::hasColumn('contracts', 'revision_note')) {
                $table->dropColumn('revision_note');
            }
            if (Schema::hasColumn('contracts', 're_sent_at')) {
                $table->dropColumn('re_sent_at');
            }
            if (Schema::hasColumn('contracts', 'edited_at')) {
                $table->dropColumn('edited_at');
            }
            if (Schema::hasColumn('contracts', 'edited_by')) {
                $table->dropConstrainedForeignId('edited_by');
            }
            if (Schema::hasColumn('contracts', 'version')) {
                $table->dropColumn('version');
            }
        });
    }
};