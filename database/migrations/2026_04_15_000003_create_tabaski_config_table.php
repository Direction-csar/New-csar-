<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabaski_config', function (Blueprint $table) {
            $table->id();
            $table->string('cle')->unique();
            $table->string('valeur');
            $table->string('label')->nullable();
            $table->timestamps();
        });

        DB::table('tabaski_config')->insert([
            ['cle' => 'date_expiration', 'valeur' => '2026-04-22 23:59:59', 'label' => 'Date limite d\'inscription', 'created_at' => now(), 'updated_at' => now()],
            ['cle' => 'inscriptions_ouvertes', 'valeur' => '1', 'label' => 'Inscriptions ouvertes', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tabaski_config');
    }
};
