<?php

namespace Database\Seeders;

use App\Models\Speech;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Publie l'allocution d'installation de la nouvelle Directrice générale.
 *
 * Le texte officiel est lu depuis database/seeders/data/allocution_dg_mame_ndeye_sene.txt
 * afin de ne jamais publier un contenu non validé. Si le fichier est absent, rien n'est créé.
 * Idempotent : réexécutable sans doublon (clé = titre).
 */
class NewDirectionSpeechSeeder extends Seeder
{
    public const TITLE = 'Allocution de Madame Mame Ndeye SENE, Directrice générale';

    public function run(): void
    {
        $file = __DIR__ . '/data/allocution_dg_mame_ndeye_sene.txt';

        if (!is_file($file)) {
            $this->command?->warn("Allocution DG non publiée : fichier manquant ($file).");
            return;
        }

        $content = trim((string) file_get_contents($file));
        if ($content === '') {
            $this->command?->warn('Allocution DG non publiée : fichier vide.');
            return;
        }

        $firstParagraph = trim(preg_split("/\R{2,}/", $content)[0] ?? '');

        Speech::updateOrCreate(
            ['title' => self::TITLE],
            [
                'author' => 'Madame Mame Ndeye SENE',
                'function' => 'Directrice générale du CSAR',
                'excerpt' => mb_substr($firstParagraph, 0, 300),
                'content' => $content,
                'date' => Carbon::parse(env('DG_SPEECH_DATE', now()->toDateString())),
                'location' => 'Siège du CSAR, Dakar',
                'summary' => 'Allocution prononcée par Madame Mame Ndeye SENE à l\'occasion de son installation en qualité de Directrice générale du Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR).',
                'portrait' => 'images/dg-mame-ndeye-sene.jpg',
            ]
        );

        $this->command?->info('Allocution DG publiée / mise à jour.');
    }
}
