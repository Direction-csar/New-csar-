<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Le contenu des actualités peut inclure des images base64 volumineuses
        // dépassant la limite de TEXT (64 Ko). On passe en LONGTEXT (4 Go).
        DB::statement('ALTER TABLE `news` MODIFY `content` LONGTEXT NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `news` MODIFY `content` TEXT NOT NULL');
    }
};
