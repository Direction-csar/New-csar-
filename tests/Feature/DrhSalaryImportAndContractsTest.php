<?php

namespace Tests\Feature;

use App\Models\Personnel;
use App\Models\RhDocument;
use App\Models\RhSalaryImport;
use App\Models\User;
use App\Services\Drh\ContractService;
use App\Services\Drh\SalaryFileParser;
use App\Services\Drh\SalaryImportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\Concerns\BuildsDistributionSchema;
use Tests\TestCase;

/** Données entièrement fictives. */
class DrhSalaryImportAndContractsTest extends TestCase
{
    use BuildsDistributionSchema;

    private User $drh;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->buildDistributionSchema();

        foreach (['rh_salary_imports', 'rh_documents', 'personnel'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::create('personnel', function (Blueprint $t) {
            $t->id();
            $t->string('prenoms_nom');
            $t->string('matricule')->unique();
            $t->string('numero_cni')->nullable();
            $t->string('adresse_complete')->nullable();
            $t->string('sexe')->nullable();
            $t->date('date_naissance')->nullable();
            $t->string('lieu_naissance')->nullable();
            $t->string('nationalite')->nullable();
            $t->integer('nombre_enfants')->nullable();
            $t->string('situation_matrimoniale')->nullable();
            $t->string('poste_actuel')->nullable();
            $t->string('categorie')->nullable();
            $t->string('type_contrat')->nullable();
            $t->date('date_recrutement_csar')->nullable();
            $t->date('date_debut_contrat')->nullable();
            $t->date('date_fin_contrat')->nullable();
            $t->string('direction_service')->nullable();
            $t->text('formations_professionnelles')->nullable();
            $t->string('diplome_academique')->nullable();
            $t->string('filiation')->nullable();
            $t->string('date_delivrance_id')->nullable();
            $t->integer('nombre_epouses')->nullable();
            foreach (['salaire_base', 'sursalaire', 'indemnite_transport', 'indemnite_fonction', 'salaire_brut', 'net_a_payer'] as $c) {
                $t->decimal($c, 15, 2)->nullable();
            }
            $t->timestamps();
        });
        Schema::create('rh_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('personnel_id')->nullable();
            $t->string('type');
            $t->string('label');
            $t->json('data')->nullable();
            $t->string('statut')->default('Enregistré');
            $t->unsignedInteger('version')->default(1);
            $t->string('file_path')->nullable();
            $t->timestamp('signed_at')->nullable();
            $t->foreignId('signed_by')->nullable();
            $t->timestamp('superseded_at')->nullable();
            $t->timestamp('exported_at')->nullable();
            $t->foreignId('created_by')->nullable();
            $t->timestamps();
        });
        Schema::create('rh_salary_imports', function (Blueprint $t) {
            $t->id();
            $t->string('filename');
            $t->string('sha256', 64);
            $t->string('status', 20)->default('preview');
            $t->json('summary')->nullable();
            $t->json('rows')->nullable();
            $t->foreignId('created_by')->nullable();
            $t->foreignId('applied_by')->nullable();
            $t->timestamp('applied_at')->nullable();
            $t->timestamps();
        });

        $this->drh = $this->makeUser('Agent DRH Fictif', 'drh');
        $this->drh->forceFill(['two_factor_enabled' => true, 'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP')])->save();
    }

    private function assertZipResponse(mixed $response): void
    {
        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertStringStartsWith('PK', file_get_contents($response->getFile()->getPathname()));
    }

    private function agent(string $matricule, string $nom, array $extra = []): Personnel
    {
        return Personnel::create(array_merge([
            'prenoms_nom' => $nom, 'matricule' => $matricule, 'numero_cni' => 'CNI-FICTIVE', 'adresse_complete' => 'Adresse fictive',
            'poste_actuel' => 'Ancien poste', 'categorie' => 'C1', 'type_contrat' => 'CDI', 'date_recrutement_csar' => '2020-01-01',
            'salaire_base' => 100000, 'sursalaire' => 50000, 'indemnite_fonction' => null, 'indemnite_transport' => 26000,
            'salaire_brut' => 150000, 'net_a_payer' => 140000,
        ], $extra));
    }

    private function block(int $ligne, string $mat, string $prenom, string $nom, int $base, int $sursal, ?int $fonct, string $poste = 'Nouveau poste'): string
    {
        $brut = $base + $sursal + ($fonct ?? 0);
        $out = "Ligne Excel {$ligne}\n  N° : {$ligne}\n  PRENOMS ET : {$prenom}\n  NOM : {$nom}\n";
        $out .= $mat !== '' ? "  MATRICULE SOLDE : {$mat}\n" : '';
        $out .= "  POSTE ACTUEL : {$poste}\n  NOUVELLE CATEGORIE CONVENTION CSAR : C3\n  CIN : 999999\n  ADRESSE : Secrète\n";
        $out .= "  Salaire brut mensuel : {$brut}\n  Salaire Catégoriel : {$base}\n  Sursalaire : {$sursal}\n";
        $out .= $fonct !== null ? "  Indemnité de fonction : {$fonct}\n" : '';
        $out .= "  Indemnité de transport (colonne 20) : 26000\n  Indemnité de transport (colonne 29) : 26000\n  NAP : 160000.5\n";

        return $out . str_repeat('-', 20) . "\n";
    }

    private function file(?string $second = null): string
    {
        $sheet = "### DIRECTION FICTIVE\n"
            . $this->block(15, '099001', 'Awa', 'FICTIF', 120000, 60000, 50000)
            . $this->block(16, '099002', 'Moussa', 'EXEMPLE', 100000, 50000, null, 'Ancien poste')
            . $this->block(17, '', 'Sans', 'MATRICULE', 100000, 50000, null)
            . $this->block(18, '777777', 'Inconnu', 'ABSENT', 100000, 50000, null)
            . $this->block(19, '099003', 'Autre', 'PERSONNE', 100000, 50000, null)
            . "Ligne Excel 20\n  PRENOMS ET : TOTAL\n  Salaire brut mensuel : 999\n";

        return "TABLEAU FICTIF\nFEUILLE : 'recap effectifs'  (2 lignes)\nDakar | 3\n"
            . "FEUILLE : 'Feuille'  (10 lignes x 31 colonnes)\n" . $sheet
            . "FEUILLE : 'Feuille  (2)'  (10 lignes x 31 colonnes)\n" . ($second ?? $sheet)
            . "FEUILLE : 'Feuil2'  (0 lignes x 0 colonnes)\n";
    }

    public function test_parser_keeps_only_salary_fields_and_detects_sheets(): void
    {
        $parsed = (new SalaryFileParser())->parse($this->file());

        $this->assertSame('Feuille', $parsed['sheet']);
        $this->assertCount(5, $parsed['agents'], 'La ligne TOTAL est ignorée');
        $this->assertSame([], $parsed['conflicts']);
        $this->assertArrayNotHasKey('cin', $parsed['agents'][0]);
        $this->assertStringNotContainsString('Secrète', json_encode($parsed));
        $this->assertSame(50000.0, $parsed['agents'][0]['indemnite_fonction']);
        $this->assertSame(160000.5, $parsed['agents'][0]['net_a_payer']);
    }

    public function test_preview_reports_statuses_without_touching_records(): void
    {
        $awa = $this->agent('99001', 'Awa FICTIF'); // zéro de tête perdu en base
        $this->agent('099002', 'Moussa EXEMPLE', ['poste_actuel' => 'Ancien poste', 'categorie' => 'C3', 'net_a_payer' => 160000.5]);
        $this->agent('099003', 'Quelqu\'un AUTREMENT');

        $import = app(SalaryImportService::class)->preview($this->file(), 'remu.txt', $this->drh->id);
        $rows = collect($import->rows)->keyBy('ligne');

        $this->assertSame('modifie', $rows[15]['status']);
        $this->assertSame($awa->id, $rows[15]['personnel_id']);
        $this->assertSame('inchange', $rows[16]['status']);
        $this->assertSame('sans_matricule', $rows[17]['status']);
        $this->assertSame('non_trouve', $rows[18]['status']);
        $this->assertContains('nom_different', $rows[19]['warnings']);
        $this->assertFalse($rows[19]['applicable']);
        $this->assertSame(1, $import->summary['applicable']);
        $this->assertEquals(150000, $awa->fresh()->salaire_brut, 'La prévisualisation ne modifie rien');
    }

    public function test_apply_updates_only_salary_fields_once(): void
    {
        $awa = $this->agent('099001', 'Awa FICTIF');
        $service = app(SalaryImportService::class);
        $import = $service->preview($this->file(), 'remu.txt', $this->drh->id);

        $result = $service->apply($import, $this->drh->id);

        $awa->refresh();
        $this->assertSame(1, $result['updated']);
        $this->assertEquals(230000, $awa->salaire_brut);
        $this->assertEquals(120000, $awa->salaire_base);
        $this->assertEquals(50000, $awa->indemnite_fonction);
        $this->assertSame('Nouveau poste', $awa->poste_actuel);
        $this->assertSame('C3', $awa->categorie);
        $this->assertSame('CNI-FICTIVE', $awa->numero_cni, 'Données personnelles inchangées');
        $this->assertSame('CDI', $awa->type_contrat);
        $this->assertSame('applied', $import->fresh()->status);
        $this->assertEquals(150000, collect($import->fresh()->rows)->firstWhere('ligne', 15)['old']['salaire_brut'], 'Ancienne valeur conservée');

        $this->expectException(RuntimeException::class);
        $service->apply($import->fresh(), $this->drh->id);
    }

    public function test_apply_skips_records_changed_since_preview(): void
    {
        $awa = $this->agent('099001', 'Awa FICTIF');
        $service = app(SalaryImportService::class);
        $import = $service->preview($this->file(), 'remu.txt', $this->drh->id);
        $awa->update(['salaire_brut' => 175000]);

        $result = $service->apply($import, $this->drh->id);

        $this->assertSame(0, $result['updated']);
        $this->assertEquals(175000, $awa->fresh()->salaire_brut);
    }

    public function test_conflicting_sheets_block_the_row(): void
    {
        $this->agent('099001', 'Awa FICTIF');
        $second = "### X\n" . $this->block(15, '099001', 'Awa', 'FICTIF', 999999, 60000, 50000);

        $import = app(SalaryImportService::class)->preview($this->file($second), 'remu.txt', null);

        $row = collect($import->rows)->firstWhere('ligne', 15);
        $this->assertContains('conflit_feuilles', $row['warnings']);
        $this->assertFalse($row['applicable']);
    }

    public function test_drh_interface_upload_preview_and_validate(): void
    {
        $awa = $this->agent('099001', 'Awa FICTIF');
        $file = UploadedFile::fake()->createWithContent('remunerations.txt', $this->file());

        $res = $this->actingAs($this->drh, 'drh')->post('/admin/drh/remunerations', ['fichier' => $file]);
        $import = RhSalaryImport::first();
        $res->assertRedirect(route('admin.drh.remunerations.show', $import));
        $this->assertEquals(150000, $awa->fresh()->salaire_brut);

        $this->actingAs($this->drh, 'drh')->get(route('admin.drh.remunerations.show', $import))
            ->assertOk()->assertSee("Valider l'actualisation", false)->assertDontSee('Secrète');

        $this->actingAs($this->drh, 'drh')->post(route('admin.drh.remunerations.apply', $import))->assertRedirect();
        $this->assertEquals(230000, $awa->fresh()->salaire_brut);
    }

    public function test_upload_rejects_non_txt_and_unauthenticated(): void
    {
        $this->post('/admin/drh/remunerations', [])->assertRedirect(route('drh.login'));

        $this->actingAs($this->drh, 'drh')
            ->post('/admin/drh/remunerations', ['fichier' => UploadedFile::fake()->create('salaires.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('fichier');
        $this->assertSame(0, RhSalaryImport::count());
    }

    public function test_contract_versioning_never_overwrites_signed_contract(): void
    {
        $agent = $this->agent('099001', 'Awa FICTIF');
        $service = app(ContractService::class);

        $this->assertSame('created', $service->generate($agent, $this->drh->id));
        $v1 = RhDocument::first();
        Storage::disk('local')->assertExists($v1->file_path);
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($v1->file_path));
        $this->assertSame('unchanged', $service->generate($agent->fresh(), $this->drh->id));
        $this->assertSame('skipped', $service->generate($agent->fresh(), $this->drh->id, true));

        // Non signé + salaire modifié : nouvelle version, l'ancienne est archivée avec son PDF.
        $agent->update(['salaire_brut' => 200000]);
        $this->assertSame('created', $service->generate($agent->fresh(), $this->drh->id));
        $v1->refresh();
        $v2 = $service->current($agent);
        $this->assertNotNull($v1->superseded_at);
        $this->assertSame(2, $v2->version);
        Storage::disk('local')->assertExists($v1->file_path);

        // Signé + modification : avenant à valider, le contrat signé reste en vigueur et intact.
        $service->markSigned($v2, $this->drh->id);
        $signedFile = Storage::disk('local')->get($v2->file_path);
        $agent->update(['salaire_brut' => 250000]);
        $this->assertSame('avenants', $service->generate($agent->fresh(), $this->drh->id));
        $v2->refresh();
        $v3 = RhDocument::where('version', 3)->first();
        $this->assertNull($v2->superseded_at);
        $this->assertSame(RhDocument::STATUT_SIGNE, $v2->statut);
        $this->assertSame($signedFile, Storage::disk('local')->get($v2->file_path));
        $this->assertSame(RhDocument::STATUT_A_VALIDER, $v3->statut);

        // Suppression d'un contrat signé refusée.
        $this->actingAs($this->drh, 'drh')->delete(route('admin.drh.documents.destroy', $v2))->assertSessionHas('error');
        $this->actingAs($this->drh, 'drh')->delete(route('admin.drh.documents.destroyAll'));
        $this->assertSame(3, RhDocument::count(), 'Signé, archivé et avenant en attente conservés');

        // Validation DRH : l'avenant devient le contrat en vigueur, le signé est archivé.
        $service->validateAvenant($v3->fresh());
        $this->assertNotNull($v2->fresh()->superseded_at);
        $this->assertSame('Généré', $v3->fresh()->statut);
    }

    public function test_remunerations_2026_seeder_updates_salary_only_and_creates_recruits(): void
    {
        Schema::table('personnel', function (Blueprint $t) {
            foreach (['tranche_age', 'contact_telephonique', 'email', 'groupe_sanguin', 'date_prise_service_csar', 'statut',
                'net_a_payer_x', 'localisation_region', 'dernier_poste_avant_csar', 'autres_diplomes_certifications',
                'logiciels_maitrises', 'langues_parlees', 'autres_aptitudes', 'aspirations_professionnelles',
                'interet_nouvelles_responsabilites', 'photo_personnelle', 'taille_vetements', 'contact_urgence_nom',
                'contact_urgence_telephone', 'contact_urgence_lien_parente', 'observations_personnelles', 'statut_validation'] as $c) {
                $t->string($c)->nullable();
            }
        });
        $awa = $this->agent('099001', 'Awa FICTIF', ['contact_telephonique' => '77 000 00 00', 'numero_cni' => 'CNI-REELLE']);

        $row = fn (string $mat, string $prenom, string $nom, int $base, int $sursal, string $fonct) => implode(';', [
            1, 'DIRECTION GENERALE', $prenom, $nom, $mat, 'Poste 2026', 'Licence', 'C3', 'Cadre', 'Adresse fictive', 'CNI-FICHIER',
            '01/01/1990 à Dakar', 'De X et Y', '02/11/2026', 'Marié', 1, $base + $sursal + (int) $fonct, $base, $sursal, $fonct, 26000,
            1, 0, 0, 0, 0, 0, 0, 0, 26000, 0, 999999.5,
        ]);
        $csv = tempnam(sys_get_temp_dir(), 'remu') ;
        file_put_contents($csv, "entete\n" . $row('099001', 'Awa', 'FICTIF', 180816, 939184, '150000') . "\n"
            . $row('202608', 'Recrue', 'FICTIVE', 100454, 303355, '') . "\n");

        $seeder = new \Database\Seeders\Remunerations2026Seeder();
        $seeder->csvPath = $csv;
        $seeder->run();
        @unlink($csv);

        $awa->refresh();
        $this->assertEquals(1270000, $awa->salaire_brut);
        $this->assertEquals(150000, $awa->indemnite_fonction);
        $this->assertSame('Poste 2026', $awa->poste_actuel);
        $this->assertSame('Direction Générale', $awa->direction_service, 'Direction selon l\'organigramme officiel');
        $this->assertSame('CNI-REELLE', $awa->numero_cni, 'Données personnelles existantes conservées');
        $this->assertSame('77 000 00 00', $awa->contact_telephonique);

        $recrue = Personnel::where('matricule', '202608')->firstOrFail();
        $this->assertEquals(403809, $recrue->salaire_brut);
        $this->assertEquals(0, $recrue->indemnite_fonction);
        $this->assertSame('Recrue FICTIVE', $recrue->prenoms_nom);
    }

    public function test_generate_all_contracts_downloads_zip_in_one_click(): void
    {
        $this->agent('099001', 'Awa FICTIF');
        $this->agent('099002', 'Moussa EXEMPLE', ['type_contrat' => 'CDD']);

        $res = $this->actingAs($this->drh, 'drh')->post(route('admin.drh.contrats.generate-all'));

        $res->assertOk();
        $this->assertZipResponse($res->baseResponse);
        $this->assertSame(2, RhDocument::count());
        $this->actingAs($this->drh, 'drh')->get(route('admin.drh.documents'))->assertOk()->assertSee('Générer tous les contrats');
    }
    public function test_personnel_update_accepts_placeholder_values(): void
    {
        $agent = $this->agent('202610', 'Awa FICTIF');

        $res = $this->actingAs($this->drh, 'drh')->put(route('admin.drh.personnel.update', $agent->id), [
            'prenoms_nom' => 'Awa CORRIGEE',
            'matricule' => '202610',
            'poste_actuel' => 'Conseiller Technique',
            'direction_service' => 'DIRECTION GENERALE',
            'adresse_complete' => 'Non renseigné',
        ]);

        $res->assertSessionDoesntHaveErrors();
        $this->assertSame('Awa CORRIGEE', $agent->fresh()->prenoms_nom);
    }

    public function test_contract_interface_generate_preview_download_zip(): void
    {
        $a = $this->agent('099001', 'Awa FICTIF');
        $b = $this->agent('099002', 'Moussa EXEMPLE', ['type_contrat' => 'CDD']);
        $as = $this->actingAs($this->drh, 'drh');

        $as->get(route('admin.drh.contrats.preview', $a))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertSame(0, RhDocument::count(), 'L\'aperçu n\'enregistre rien');

        $as->post(route('admin.drh.contrats.generate'), ['mode' => 'selection', 'agents' => [$b->id]])->assertRedirect();
        $this->assertSame('contrat-cdd', RhDocument::first()->type);

        $as->post(route('admin.drh.contrats.generate'), ['mode' => 'missing'])->assertRedirect();
        $this->assertSame(2, RhDocument::count());

        $as->get(route('admin.drh.contrats.index'))->assertOk()->assertSee('Awa FICTIF');

        $doc = RhDocument::where('personnel_id', $a->id)->first();
        $pdf = $as->get(route('admin.drh.contrats.download', $doc));
        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());

        $zip = $as->post(route('admin.drh.contrats.zip'));
        $zip->assertOk();
        $this->assertZipResponse($zip->baseResponse);
    }
}
