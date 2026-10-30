<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trace l'agent distributeur qui a emis le ticket (statistiques par agent).
 * Nullable : les tickets deja emis restent valides sans emetteur connu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribution_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('distribution_tickets', 'issued_by')) {
                $table->foreignId('issued_by')->nullable()
                    ->after('scanned_by')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('distribution_tickets', function (Blueprint $table) {
            if (Schema::hasColumn('distribution_tickets', 'issued_by')) {
                $table->dropConstrainedForeignId('issued_by');
            }
        });
    }
};
