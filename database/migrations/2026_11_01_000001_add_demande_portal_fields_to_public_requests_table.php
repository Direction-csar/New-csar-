<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('public_requests', function (Blueprint $table) {
            $table->string('depot_mode', 20)->nullable()->after('type');
            $table->string('demandeur_type', 20)->nullable()->after('depot_mode');
            $table->string('cni', 30)->nullable()->after('demandeur_type');
            $table->string('ninea', 30)->nullable()->after('cni');
            $table->string('departement', 120)->nullable()->after('region');
            $table->string('commune', 120)->nullable()->after('departement');
            $table->string('letter_path')->nullable()->after('commune');
            $table->json('attachments')->nullable()->after('letter_path');
        });
    }

    public function down(): void
    {
        Schema::table('public_requests', function (Blueprint $table) {
            $table->dropColumn([
                'depot_mode', 'demandeur_type', 'cni', 'ninea',
                'departement', 'commune', 'letter_path', 'attachments',
            ]);
        });
    }
};
