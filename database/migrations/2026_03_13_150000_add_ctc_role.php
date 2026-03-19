<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('roles')->where('name', 'ctc')->exists();
        if (!$exists) {
            DB::table('roles')->insert([
                'name' => 'ctc',
                'display_name' => 'Conseil Technique de la Communication',
                'description' => 'Gestion des publications, actualités, rapports, newsletter et contenu public',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('roles')->where('name', 'ctc')->delete();
    }
};
