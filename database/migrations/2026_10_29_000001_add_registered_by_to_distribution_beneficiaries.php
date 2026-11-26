<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajoute la colonne registered_by : l'API mobile l'envoie déjà
 * (storeBeneficiaryV2 / batch) mais la colonne n'existait pas,
 * la valeur était silencieusement perdue.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribution_beneficiaries', function (Blueprint $table) {
            if (!Schema::hasColumn('distribution_beneficiaries', 'registered_by')) {
                $table->foreignId('registered_by')->nullable()
                    ->after('validated_by')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('distribution_beneficiaries', function (Blueprint $table) {
            if (Schema::hasColumn('distribution_beneficiaries', 'registered_by')) {
                $table->dropConstrainedForeignId('registered_by');
            }
        });
    }
};
