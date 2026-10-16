<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            if (!Schema::hasColumn('personnel', 'date_debut_contrat')) {
                $table->date('date_debut_contrat')->nullable()->after('type_contrat');
            }
            if (!Schema::hasColumn('personnel', 'date_fin_contrat')) {
                $table->date('date_fin_contrat')->nullable()->after('date_debut_contrat');
                $table->index('date_fin_contrat', 'personnel_date_fin_contrat_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            if (Schema::hasColumn('personnel', 'date_fin_contrat')) {
                $table->dropIndex('personnel_date_fin_contrat_idx');
                $table->dropColumn('date_fin_contrat');
            }
            if (Schema::hasColumn('personnel', 'date_debut_contrat')) {
                $table->dropColumn('date_debut_contrat');
            }
        });
    }
};
