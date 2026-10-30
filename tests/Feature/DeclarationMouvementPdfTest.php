<?php

namespace Tests\Feature;

use App\Models\RhDocument;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\BuildsDistributionSchema;
use Tests\TestCase;

class DeclarationMouvementPdfTest extends TestCase
{
    use BuildsDistributionSchema;

    public function test_export_pdf_deux_pages(): void
    {
        $this->buildDistributionSchema();
        Schema::create('rh_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('personnel_id')->nullable();
            $t->string('type');
            $t->string('label');
            $t->json('data')->nullable();
            $t->string('statut')->default('Enregistré');
            $t->foreignId('created_by')->nullable();
            $t->timestamps();
            $t->timestamp('exported_at')->nullable();
        });

        $drh = $this->makeUser('Agent DRH Test', 'drh');
        $drh->forceFill(['two_factor_enabled' => true, 'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP')])->save();

        $doc = RhDocument::create([
            'type' => 'declaration-mouvement-travailleur',
            'label' => 'Déclaration de mouvement du travailleur',
            'data' => [
                'nom' => 'FICTIF', 'prenoms' => 'Agent Démo', 'sexe' => 'M',
                'date_naissance' => '1990-01-15', 'lieu_naissance' => 'Dakar',
                'adresse' => 'Parcelles Assainies', 'salaire_base' => '250 000',
                'dispositions_particulieres' => 'Texte fictif.',
            ],
            'created_by' => $drh->id,
        ]);

        $res = $this->actingAs($drh, 'drh')->get("/admin/drh/documents/{$doc->id}/export");
        $res->assertOk();
        $content = $res->getContent();
        $this->assertStringStartsWith('%PDF', $content);
        $this->assertSame('Exporté', $doc->fresh()->statut);

        // Dompdf ecrit chaque page comme un objet « /Type /Page » (singulier).
        preg_match_all('/\/Type\s*\/Page(?![a-z])/i', $content, $m);
        $this->assertCount(2, $m[0], 'Le PDF doit contenir exactement 2 objets page');
    }
}
