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
    public function test_contracts_single_pdf_with_first_page_only_option(): void
    {
        $this->agent('099001', 'Awa FICTIF');
        $this->agent('099002', 'Moussa EXEMPLE', ['type_contrat' => 'CDD']);

        foreach (['all', 'first'] as $pages) {
            $res = $this->actingAs($this->drh, 'drh')->post(route('admin.drh.contrats.pdf-unique'), ['pages' => $pages]);
            $res->assertOk();
            $this->assertSame('application/pdf', $res->headers->get('Content-Type'));
            $this->assertStringStartsWith('%PDF', $res->baseResponse->getContent());
        }

        $docs = RhDocument::contracts()->current()->with('personnel')->get();
        $svc = app(\App\Services\Drh\ContractService::class);
        $this->assertStringContainsString('ARTICLE 4', $svc->combinedHtml($docs, 'all'));
        $first = $svc->combinedHtml($docs, 'first');
        $this->assertStringNotContainsString('ARTICLE 4', $first);
        $this->assertStringContainsString('ARTICLE 1', $first);
        $this->assertSame(2, substr_count($first, 'ARTICLE 1 :'));
        $this->actingAs($this->drh, 'drh')->get(route('admin.drh.documents'))->assertOk()->assertSee('Page 1 seulement');
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

    public function test_contract_text_uses_affectation_and_accented_situation(): void
    {
        $this->assertSame('Mariée', Personnel::libelleSituation('Marie', 'Feminin'));
        $this->assertSame('Marié', Personnel::libelleSituation('Marié (e)', 'Masculin'));
        $this->assertSame('Célibataire', Personnel::libelleSituation('Celibataire', 'Masculin'));
        $this->assertSame('Divorcée', Personnel::libelleSituation('Divorce', 'Féminin'));
        $this->assertSame('Inspection régionale de Kaolack', Personnel::lieuAffectation('Inspection régionale de Kaolack'));
        $this->assertStringStartsWith('Dakar, 2 Rue', Personnel::lieuAffectation('Cellule Informatique'));
        $this->assertSame('Thies, Inspection régionale du CSAR', Personnel::lieuAffectation('IR', 'Thies'));

        $agent = new Personnel([
            'prenoms_nom' => 'Awa FICTIVE', 'sexe' => 'Feminin', 'situation_matrimoniale' => 'Marie',
            'direction_service' => 'Inspection régionale de Kaolack', 'poste_actuel' => 'Secrétaire',
        ]);
        foreach (['contrat_cdi', 'contrat_cdd'] as $view) {
            $html = view("admin.drh.documents.$view", ['personnel' => $agent])->render();
            $this->assertStringContainsString("à l'Inspection régionale de Kaolack du Commissariat à", $html, $view);
            $this->assertStringContainsString('Inspection régionale de Kaolack', $html, $view);
            $this->assertStringContainsString('Situation de Famille : Mariée', $html, $view);
            $this->assertStringNotContainsString('22 Rue', $html, $view);
        }
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

    public function test_fiches_2026_seeder_updates_diploma_and_sexe(): void
    {
        Schema::table('personnel', function (Blueprint $t) {
            foreach (['tranche_age', 'contact_telephonique', 'email', 'groupe_sanguin', 'date_prise_service_csar', 'statut',
                'localisation_region', 'dernier_poste_avant_csar', 'autres_diplomes_certifications',
                'logiciels_maitrises', 'langues_parlees', 'autres_aptitudes', 'aspirations_professionnelles',
                'interet_nouvelles_responsabilites', 'photo_personnelle', 'taille_vetements', 'contact_urgence_nom',
                'contact_urgence_telephone', 'contact_urgence_lien_parente', 'observations_personnelles', 'statut_validation'] as $c) {
                $t->string($c)->nullable();
            }
        });

        // Fiches existantes avec les anciennes valeurs erronées.
        $this->agent('202503', 'Lamine Séye DANFA', [
            'formations_professionnelles' => "d'un Brevet de Fin d'Études Moyennes (BFEM)",
            'diplome_academique' => 'BFEM', 'sexe' => 'Feminin',
        ]);
        $this->agent('202301', 'Mame Libass Laye FAYE', ['sexe' => 'Feminin']);
        $this->agent('202204', 'AIDA DIEYE', ['sexe' => 'Masculin']);
        $this->agent('020146', 'Modou Astou Djeumb MBOW', ['sexe' => 'Feminin']);
        // Yandé NDIAYE est une femme (« Mariée » correcte dans le tableau) ;
        // Baboucar COLY est un homme malgré le « Mariée » erroné du tableau.
        $this->agent('020078', 'Yandé NDIAYE', ['sexe' => 'Masculin']);
        $this->agent('098705', 'Baboucar COLY', ['sexe' => 'Feminin']);

        // Extrait les lignes réelles du tableau 2026 pour ces agents.
        $lines = file(database_path('seeders/fiches_2026.csv'));
        $keep = ['202503', '202301', '202204', '020146', '020078', '098705'];
        $csv = tempnam(sys_get_temp_dir(), 'fiches');
        file_put_contents($csv, $lines[0] . implode('', array_filter(
            array_slice($lines, 1),
            fn ($l) => in_array(str_getcsv($l, ';')[4] ?? '', $keep, true)
        )));

        $seeder = new \Database\Seeders\FichesAgents2026Seeder();
        $seeder->csvPath = $csv;
        $seeder->run();
        @unlink($csv);

        $danfa = Personnel::where('matricule', '202503')->firstOrFail();
        $this->assertStringContainsString('Baccalauréat', $danfa->formations_professionnelles);
        $this->assertSame('Baccalaureat', $danfa->diplome_academique);
        $this->assertSame('Masculin', $danfa->sexe, 'Lamine Séye DANFA est un homme');
        $this->assertSame('Cellule Communication', $danfa->direction_service);

        // Le sexe est déduit du premier prénom : « Mame », « Sadio », « Modou Astou » sont masculins,
        // « AIDA » féminin même si le tableau dit « Célibataire ».
        $this->assertSame('Masculin', Personnel::where('matricule', '202301')->firstOrFail()->sexe);
        $this->assertSame('Masculin', Personnel::where('matricule', '020146')->firstOrFail()->sexe);
        $this->assertSame('Feminin', Personnel::where('matricule', '202204')->firstOrFail()->sexe);
        $this->assertSame('Feminin', Personnel::where('matricule', '020078')->firstOrFail()->sexe, 'Yandé NDIAYE (DAF) est une femme (« Mariée » dans le tableau)');
        $this->assertSame('Masculin', Personnel::where('matricule', '098705')->firstOrFail()->sexe, 'Baboucar COLY est un homme malgré « Mariée »');

        $html = view('admin.drh.documents.contrat_cdi', [
            'personnel' => $danfa, 'diplome' => $danfa->formations_professionnelles,
        ])->render();
        $this->assertStringContainsString('Baccalauréat', $html);
        $this->assertStringContainsString('Monsieur', $html);
        $this->assertStringNotContainsString("d&#039;un(e) d&#039;un", $html, 'Pas de double préfixe devant « d\'un Baccalauréat »');
        $this->assertStringContainsString('titulaire d&#039;un Baccalauréat', $html);

        // Un diplôme sans article (« Gardien ») reçoit toujours « d'un(e) ».
        $gardien = new Personnel(['prenoms_nom' => 'Aliou BA', 'sexe' => 'Masculin']);
        $html = view('admin.drh.documents.contrat_cdi', ['personnel' => $gardien, 'diplome' => 'Gardien'])->render();
        $this->assertStringContainsString("titulaire d&#039;un(e) Gardien", $html);
    }

    public function test_corrections_drh_listees_fiches_contrats_et_dmt(): void
    {
        Schema::table('personnel', function (Blueprint $t) {
            foreach (['tranche_age', 'contact_telephonique', 'email', 'groupe_sanguin', 'date_prise_service_csar', 'statut',
                'localisation_region', 'dernier_poste_avant_csar', 'autres_diplomes_certifications',
                'logiciels_maitrises', 'langues_parlees', 'autres_aptitudes', 'aspirations_professionnelles',
                'interet_nouvelles_responsabilites', 'photo_personnelle', 'taille_vetements', 'contact_urgence_nom',
                'contact_urgence_telephone', 'contact_urgence_lien_parente', 'observations_personnelles', 'statut_validation'] as $c) {
                $t->string($c)->nullable();
            }
        });

        $this->agent('202606', 'Oumar Abdourahmane SALL', ['type_contrat' => 'CDD']);
        $this->agent('099011', 'Moussa DIOP', ['filiation' => 'De Mayoro et Astou WADE']);
        $this->agent('202102', 'Souadou KA', ['nombre_enfants' => 3]);
        $this->agent('020143', 'Mohamed Sileye DIOP');
        $this->agent('007020', 'Babou THIAM');
        $this->agent('202502', 'Ousmane THIAM', ['lieu_naissance' => 'Paoscoto']);
        $this->agent('020078', 'Yandé NDIAYE', ['formations_professionnelles' => "d'un Master en Finance publique"]);
        $this->agent('202202', 'Soumaïla KEITA', ['formations_professionnelles' => "d'un Master en Prévention et Gestion des Risques Agricoles liés à la Sécurité"]);
        $this->agent('000598', 'Ibrahima DIAGNE', ['nombre_enfants' => 2]);
        $this->agent('099022', 'Ibrahima DIOP');
        $this->agent('000633', 'Lamine NDIAYE', ['filiation' => 'De Pathé et Gnilane']);
        $this->agent('202602', 'Mohamed SOW');
        $this->agent('202503', 'Lamine Séye DANFA');

        $lines = file(database_path('seeders/fiches_2026.csv'));
        $keep = ['202606', '099011', '202102', '020143', '007020', '202502', '020078', '202202', '000598', '099022', '202602', '202503', '000633'];
        $csv = tempnam(sys_get_temp_dir(), 'fiches');
        file_put_contents($csv, $lines[0] . implode('', array_filter(
            array_slice($lines, 1), fn ($l) => in_array(str_getcsv($l, ';')[4] ?? '', $keep, true)
        )));
        $seeder = new \Database\Seeders\FichesAgents2026Seeder();
        $seeder->csvPath = $csv;
        $seeder->run();
        @unlink($csv);

        $p = fn ($m) => Personnel::where('matricule', $m)->firstOrFail();
        $this->assertSame('CDI', $p('202606')->type_contrat);
        $this->assertStringContainsString('Fatou WADE', $p('099011')->filiation);
        $this->assertSame(4, (int) $p('202102')->nombre_enfants);
        $this->assertSame('Mouhamadou Sileye DIOP', $p('020143')->prenoms_nom);
        $this->assertSame(1, (int) $p('007020')->nombre_epouses);
        $this->assertSame(1, (int) $p('202502')->nombre_epouses);
        $this->assertSame('Taïba Niassene, arrondissement de Paoscoto', $p('202502')->lieu_naissance);
        $this->assertStringContainsString('Finances et Gestion Publique', $p('020078')->formations_professionnelles);

        $this->assertStringContainsString('Risques liés à la Sécurité Alimentaire', $p('202202')->formations_professionnelles);
        $this->assertStringNotContainsString('Agricoles', $p('202202')->formations_professionnelles);
        $this->assertSame(6, (int) $p('000598')->nombre_enfants);
        $this->assertSame('De Pathé et Gnilane SARR', $p('000633')->filiation);
        $this->assertSame('Gnilane SARR', app(\App\Services\Drh\AgentDocumentData::class)->defaults($p('000633'))['mere']);
        $this->assertStringContainsString('Master en Sciences et Techniques des Ressources Humaines', $p('099022')->formations_professionnelles);
        $this->assertSame('Administrateur des Systèmes, bases de données et plateformes numériques', $p('202602')->poste_actuel);
        $this->assertSame('Assistant Chef de la Cellule de la Communication', $p('202503')->poste_actuel);
        $html = view('admin.drh.documents.contrat_cdi', ['personnel' => $p('202503'), 'poste' => $p('202503')->poste_actuel])->render();
        $this->assertStringContainsString("en qualité d'Assistant Chef de la Cellule de la Communication", html_entity_decode($html, ENT_QUOTES));
        $this->assertStringNotContainsString('Photographe', $html);

        $defaults = app(\App\Services\Drh\AgentDocumentData::class)->defaults($p('202502'));
        $this->assertSame(1, (int) $defaults['nombre_epouses']);
        $this->assertSame('Paoscoto', explode('arrondissement de ', $defaults['lieu_naissance'])[1]);
        $this->assertSame('indeterminee', app(\App\Services\Drh\AgentDocumentData::class)->defaults($p('202606'))['duree_contrat_type']);
        $this->assertSame('contrat-cdi', app(\App\Services\Drh\ContractService::class)->typeFor($p('202606')));
    }

    public function test_cdi_header_is_avenant_except_four_2026_recruits(): void
    {
        $render = fn (string $m) => view('admin.drh.documents.contrat_cdi', [
            'personnel' => new Personnel(['prenoms_nom' => 'Awa FICTIVE', 'matricule' => $m, 'sexe' => 'Feminin']),
        ])->render();

        foreach (['099001', '020142'] as $m) {
            $html = $render($m);
            $this->assertStringContainsString('AVENANT AU CONTRAT DE TRAVAIL', $html);
            $this->assertStringContainsString('A DUREE INDETERMINEE', $html);
            $this->assertStringContainsString('en sa qualité de Directeur Général', $html);
        }
        foreach (['202609', '202608', '202610', '202606'] as $m) {
            $html = $render($m);
            $this->assertStringNotContainsString('AVENANT', $html);
            $this->assertStringContainsString('CONTRAT DE TRAVAIL', $html);
            $this->assertStringContainsString('en sa qualité de Directeur Général', $html);
        }
    }

    public function test_document_edit_updates_in_place_and_changes_agent(): void
    {
        $a = $this->agent('099001', 'Awa FICTIF');
        $b = $this->agent('099002', 'Moussa EXEMPLE');

        $this->actingAs($this->drh, 'drh')->post(route('admin.drh.documents.save', 'contrat-cdi'), [
            'personnel_id' => $a->id, 'date_embauche' => '2020-01-01', 'salaire_base' => 100000,
        ])->assertRedirect();
        $doc = RhDocument::firstOrFail();
        $this->assertSame($a->id, $doc->personnel_id);

        // Modification en place : pas de doublon, données et agent mis à jour.
        $this->actingAs($this->drh, 'drh')->post(route('admin.drh.documents.save', 'contrat-cdi'), [
            'document_id' => $doc->id, 'personnel_id' => $b->id,
            'date_embauche' => '2021-05-05', 'salaire_base' => 200000,
        ])->assertRedirect();

        $this->assertSame(1, RhDocument::count());
        $doc->refresh();
        $this->assertSame($b->id, $doc->personnel_id, 'Agent modifié');
        $this->assertSame('2021-05-05', $doc->data['date_embauche']);
        $this->assertEquals(200000, $doc->data['salaire_base']);
        $this->assertNotNull($doc->file_path, 'Le PDF du contrat est régénéré');
        Storage::disk('local')->assertExists($doc->file_path);

        // L'export reflète les modifications.
        $pdf = $this->actingAs($this->drh, 'drh')->get(route('admin.drh.documents.export', $doc));
        $pdf->assertOk();

        // Un contrat signé n'est plus modifiable.
        app(ContractService::class)->markSigned($doc->fresh(), $this->drh->id);
        $this->actingAs($this->drh, 'drh')->post(route('admin.drh.documents.save', 'contrat-cdi'), [
            'document_id' => $doc->id, 'salaire_base' => 999999,
        ])->assertSessionHas('error');
        $this->assertEquals(200000, $doc->fresh()->data['salaire_base']);
    }

    public function test_personnel_update_syncs_unsigned_documents(): void
    {
        $agent = $this->agent('099001', 'Awa FICTIF', [
            'poste_actuel' => 'Assistant', 'salaire_base' => 100000,
        ]);
        $defaults = app(\App\Services\Drh\AgentDocumentData::class)->defaults($agent);

        // Contrat généré depuis la fiche + DMT avec une valeur saisie à la main.
        RhDocument::create([
            'type' => 'contrat-cdi', 'label' => 'Contrat CDI', 'personnel_id' => $agent->id,
            'data' => $defaults,
        ]);
        $dmtData = array_merge($defaults, ['salaire_base' => 777777]); // saisie manuelle différente
        $dmt = RhDocument::create([
            'type' => 'declaration-mouvement-travailleur', 'label' => 'DMT',
            'personnel_id' => $agent->id, 'data' => $dmtData,
        ]);

        $avant = clone $agent;
        $agent->update(['poste_actuel' => 'Chef de service', 'salaire_base' => 200000]);

        $updated = app(\App\Services\Drh\AgentDocumentSync::class)->sync($avant, $agent);

        $this->assertSame(2, $updated);
        $contrat = RhDocument::where('type', 'contrat-cdi')->firstOrFail();
        $this->assertSame('Chef de service', $contrat->data['poste']);
        $this->assertSame('Chef de service', $contrat->data['profession'] ?? 'Chef de service');
        $this->assertEquals(200000, $contrat->data['salaire_base']);

        // La valeur manuelle de la DMT est conservée.
        $this->assertEquals(777777, $dmt->fresh()->data['salaire_base']);
        // …mais les champs non modifiés à la main suivent la fiche.
        $this->assertSame('Chef de service', $dmt->fresh()->data['profession']);
    }
}
