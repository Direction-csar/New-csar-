<?php

namespace Tests\Feature;

use App\Models\DistributionBeneficiary;
use App\Models\User;
use App\Services\Distribution\BeneficiaryImportService;
use App\Services\Distribution\ImportException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\Concerns\BuildsDistributionSchema;
use Tests\TestCase;

/**
 * Import Excel (XLS / XLSX), CSV et liste collee : apercu sans ecriture, controle des
 * colonnes, doublons, erreurs, confirmation. Donnees FICTIVES, SQLite :memory:.
 */
class DistributionImportTest extends TestCase
{
    use BuildsDistributionSchema;

    private User $admin;
    private array $tmp = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->buildDistributionSchema();
        Storage::fake('local');

        $this->admin = $this->makeUser('Admin Import', 'admin');
        $this->admin->forceFill(['two_factor_enabled' => true, 'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP')])->save();
    }

    protected function tearDown(): void
    {
        foreach ($this->tmp as $f) {
            @unlink($f);
        }
        parent::tearDown();
    }

    /** @param array<int, array<int, mixed>> $rows */
    private function workbook(array $rows, string $format = 'xlsx'): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($rows, null, 'A1', true);
        $path = tempnam(sys_get_temp_dir(), 'csar_test_') . '.' . $format;
        ($format === 'xls' ? new Xls($spreadsheet) : new Xlsx($spreadsheet))->save($path);
        $this->tmp[] = $path;

        return $path;
    }

    private function upload(string $path, string $name): UploadedFile
    {
        return new UploadedFile($path, $name, null, null, true);
    }

    private function preview(array $payload, ?UploadedFile $file = null)
    {
        $data = $payload + ($file ? ['import_file' => $file] : []);

        return $this->actingAs($this->admin, 'admin')
            ->post('/admin/distribution/beneficiaries/import', $data);
    }

    private function headerRows(): array
    {
        return [
            ['Nom complet', 'Téléphone', 'CNI', 'Quantité (kg)', 'Catégorie', 'Adresse'],
            ['Awa Fictive', 771234567, '1234567890123', '50,5', 'veuve', 'Quartier A'],
            ['Moussa Fictif', '77 123 45 68', '', 40, '', ''],
            ['', '770000000', '', 10, '', ''],
            ['Cheikh Fictif', 'abc', '', 10, '', ''],
            ['Fama Fictive', '', '', 'beaucoup', '', ''],
            ['Awa Fictive', '779999999', '', 30, '', ''],
            ['Ibou Fictif', '', '', 20, '', ''],
        ];
    }

    // ---------- Apercu ----------

    public function test_apercu_xlsx_ne_cree_rien_et_classe_les_lignes(): void
    {
        $p = $this->makePlanning('P');
        $file = $this->upload($this->workbook($this->headerRows()), 'liste.xlsx');

        $res = $this->preview(['planning_id' => $p->id, 'category' => 'famille'], $file)->assertOk();

        $analysis = $res->viewData('analysis');
        $this->assertSame(0, DistributionBeneficiary::count());
        $this->assertSame(['total' => 7, 'ok' => 3, 'duplicate' => 1, 'error' => 3], $analysis['counts']);
        $this->assertTrue($analysis['header_detected']);

        $byName = collect($analysis['rows'])->keyBy('line');
        $this->assertSame('771234567', $byName[2]['phone']);          // cellule numerique Excel
        $this->assertSame(50.5, $byName[2]['quantity_kg']);            // virgule decimale
        $this->assertSame('veuve', $byName[2]['category']);
        $this->assertSame('77123456' . '8', $byName[3]['phone']);      // espaces retires
        $this->assertSame('famille', $byName[3]['category']);          // categorie par defaut
        $this->assertSame('error', $byName[4]['status']);              // nom vide
        $this->assertSame('error', $byName[5]['status']);              // telephone invalide
        $this->assertSame('error', $byName[6]['status']);              // quantite invalide
        $this->assertSame('duplicate', $byName[7]['status']);          // meme nom que la ligne 2
        $this->assertStringContainsString('ligne 2', $byName[7]['messages'][0]);
        $this->assertNotNull($res->viewData('token'));
    }

    public function test_apercu_signale_les_doublons_deja_presents_dans_le_planning(): void
    {
        $p = $this->makePlanning('P');
        $other = $this->makePlanning('Autre');
        DistributionBeneficiary::create(['planning_id' => $p->id, 'full_name' => 'awa fictive', 'quantity_kg' => 10, 'status' => 'pending']);
        DistributionBeneficiary::create(['planning_id' => $p->id, 'full_name' => 'Autre Nom', 'phone' => '77123456' . '8', 'quantity_kg' => 10, 'status' => 'pending']);
        DistributionBeneficiary::create(['planning_id' => $other->id, 'full_name' => 'Ibou Fictif', 'quantity_kg' => 10, 'status' => 'pending']);

        $res = $this->preview(['planning_id' => $p->id], $this->upload($this->workbook($this->headerRows()), 'liste.xlsx'))->assertOk();
        $byLine = collect($res->viewData('analysis')['rows'])->keyBy('line');

        $this->assertSame('duplicate', $byLine[2]['status']);  // meme nom (casse ignoree)
        $this->assertSame('duplicate', $byLine[3]['status']);  // meme telephone
        $this->assertSame('ok', $byLine[8]['status']);         // meme nom mais dans un AUTRE planning : accepte
    }

    public function test_apercu_xls_ancien_format_et_en_tetes_prenom_nom(): void
    {
        $p = $this->makePlanning('P');
        $path = $this->workbook([
            ['Prénom', 'Nom', 'Tel', 'Kg'],
            ['Awa', 'Fictive', '771111111', 25],
        ], 'xls');

        $res = $this->preview(['planning_id' => $p->id], $this->upload($path, 'ancien.xls'))->assertOk();
        $row = $res->viewData('analysis')['rows'][0];

        $this->assertSame('Awa Fictive', $row['name']);
        $this->assertSame('771111111', $row['phone']);
        $this->assertSame(25.0, $row['quantity_kg']);
    }

    public function test_formule_excel_refusee_sans_etre_evaluee(): void
    {
        $p = $this->makePlanning('P');
        $path = $this->workbook([['Nom', 'Quantité'], ['=1+1', 10], ['Ligne Saine', 10]]);

        $rows = $this->preview(['planning_id' => $p->id], $this->upload($path, 'f.xlsx'))->viewData('analysis')['rows'];

        $this->assertSame('error', $rows[0]['status']);
        $this->assertStringContainsString('Formule', $rows[0]['messages'][0]);
        $this->assertSame('ok', $rows[1]['status']);
    }

    // ---------- Colonnes et fichiers invalides ----------

    public function test_sans_colonne_nom_l_import_est_refuse_avec_un_message_clair(): void
    {
        $p = $this->makePlanning('P');
        $path = $this->workbook([['Prénom', 'Téléphone'], ['Awa', '771111111']]);

        $this->preview(['planning_id' => $p->id], $this->upload($path, 'f.xlsx'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'Colonne « nom » introuvable'));
        $this->assertSame(0, DistributionBeneficiary::count());
    }

    public function test_fichier_corrompu_extension_inconnue_et_double_source_sont_refuses(): void
    {
        $p = $this->makePlanning('P');
        $bad = tempnam(sys_get_temp_dir(), 'csar_bad_') . '.xlsx';
        file_put_contents($bad, 'ceci n\'est pas un classeur Excel');
        $this->tmp[] = $bad;

        $this->preview(['planning_id' => $p->id], $this->upload($bad, 'casse.xlsx'))
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'illisible'));

        $exe = tempnam(sys_get_temp_dir(), 'csar_exe_');
        $this->tmp[] = $exe;
        $this->preview(['planning_id' => $p->id], $this->upload($exe, 'virus.exe'))->assertSessionHasErrors('import_file');

        $ok = $this->upload($this->workbook([['Nom'], ['X']]), 'ok.xlsx');
        $this->preview(['planning_id' => $p->id, 'list_text' => 'A;1'], $ok)
            ->assertSessionHas('error', fn ($m) => str_contains($m, 'soit un fichier, soit une liste'));
    }

    public function test_en_tetes_seuls_ou_fichier_vide_sont_refuses(): void
    {
        $service = new BeneficiaryImportService();

        $this->expectException(ImportException::class);
        $service->analyze([['Nom', 'Téléphone']], 1);
    }

    public function test_csv_point_virgule_windows_1252_et_liste_collee_positionnelle(): void
    {
        $service = new BeneficiaryImportService();
        $csv = mb_convert_encoding("Nom;Téléphone;Quantité\nÉlodie Fictive;771111111;30\n", 'Windows-1252', 'UTF-8');

        $a = $service->analyze($service->readDelimited($csv), 1);
        $this->assertSame('Élodie Fictive', $a['rows'][0]['name']);
        $this->assertSame(30.0, $a['rows'][0]['quantity_kg']);

        // Ancien format (sans en-tete) : nom;telephone;cni;quantite
        $b = $service->analyze($service->readDelimited("Ibou Fictif;771111112;1234567890123;45\nSans Quantite;;;"), 1, 'general', 20);
        $this->assertFalse($b['header_detected']);
        $this->assertSame(45.0, $b['rows'][0]['quantity_kg']);
        $this->assertSame(20.0, $b['rows'][1]['quantity_kg']);   // quantite par defaut
    }

    public function test_limite_de_lignes(): void
    {
        $service = new BeneficiaryImportService();
        $lines = "Nom\n" . implode("\n", array_map(fn ($i) => "Personne $i", range(1, BeneficiaryImportService::MAX_ROWS + 1)));

        $this->expectException(ImportException::class);
        $service->readDelimited($lines);
    }

    // ---------- Confirmation ----------

    public function test_confirmation_importe_uniquement_les_lignes_valides_avec_tracabilite(): void
    {
        $p = $this->makePlanning('P');
        $res = $this->preview(['planning_id' => $p->id], $this->upload($this->workbook($this->headerRows()), 'liste.xlsx'));
        $token = $res->viewData('token');

        $this->actingAs($this->admin, 'admin')
            ->post('/admin/distribution/beneficiaries/import/confirm', ['token' => $token])
            ->assertRedirect();

        $this->assertSame(3, DistributionBeneficiary::count());
        $this->assertEqualsCanonicalizing(['Awa Fictive', 'Moussa Fictif', 'Ibou Fictif'], DistributionBeneficiary::pluck('full_name')->all());
        $this->assertSame(['pending'], DistributionBeneficiary::pluck('status')->unique()->all());
        $this->assertSame([$this->admin->id], DistributionBeneficiary::pluck('registered_by')->map(fn ($v) => (int) $v)->unique()->all());
        $this->assertSame([$p->id], DistributionBeneficiary::pluck('planning_id')->map(fn ($v) => (int) $v)->unique()->all());

        // Le jeton est a usage unique.
        $this->actingAs($this->admin, 'admin')
            ->post('/admin/distribution/beneficiaries/import/confirm', ['token' => $token])
            ->assertRedirect(route('admin.distribution.beneficiaries.import'));
        $this->assertSame(3, DistributionBeneficiary::count());
    }

    public function test_confirmation_recontrole_les_doublons_ajoutes_apres_l_apercu(): void
    {
        $p = $this->makePlanning('P');
        $res = $this->preview(['planning_id' => $p->id, 'default_quantity_kg' => 10], $this->upload($this->workbook([['Nom'], ['Awa Fictive'], ['Ibou Fictif']]), 'l.xlsx'));
        $token = $res->viewData('token');

        DistributionBeneficiary::create(['planning_id' => $p->id, 'full_name' => 'Awa Fictive', 'quantity_kg' => 10, 'status' => 'pending']);

        $this->actingAs($this->admin, 'admin')->post('/admin/distribution/beneficiaries/import/confirm', ['token' => $token]);

        $this->assertSame(1, DistributionBeneficiary::where('full_name', 'Awa Fictive')->count());
        $this->assertSame(1, DistributionBeneficiary::where('full_name', 'Ibou Fictif')->count());
    }

    public function test_jeton_d_un_autre_utilisateur_ou_invalide_est_refuse(): void
    {
        $p = $this->makePlanning('P');
        $token = $this->preview(['planning_id' => $p->id, 'default_quantity_kg' => 5, 'list_text' => "Nom\nAwa Fictive"])->viewData('token');
        $this->assertNotNull($token);

        $intruder = $this->makeUser('Autre Admin', 'admin');
        $intruder->forceFill(['two_factor_enabled' => true, 'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP')])->save();

        $this->actingAs($intruder, 'admin')->post('/admin/distribution/beneficiaries/import/confirm', ['token' => $token])
            ->assertRedirect(route('admin.distribution.beneficiaries.import'));
        $this->actingAs($this->admin, 'admin')->post('/admin/distribution/beneficiaries/import/confirm', ['token' => '../../etc/passwd'])
            ->assertRedirect(route('admin.distribution.beneficiaries.import'));
        $this->assertSame(0, DistributionBeneficiary::count());
    }

    public function test_aucune_ligne_valide_pas_de_jeton_de_confirmation(): void
    {
        $p = $this->makePlanning('P');
        $res = $this->preview(['planning_id' => $p->id], $this->upload($this->workbook([['Nom', 'Téléphone'], ['', 'abc']]), 'v.xlsx'))->assertOk();

        $this->assertNull($res->viewData('token'));
    }

    // ---------- Modele ----------

    public function test_le_modele_telecharge_est_un_xlsx_reimportable(): void
    {
        $res = $this->actingAs($this->admin, 'admin')->get('/admin/distribution/beneficiaries/import/template')->assertOk();

        $path = $res->baseResponse->getFile()->getPathname();
        $copy = tempnam(sys_get_temp_dir(), 'csar_tpl_') . '.xlsx';
        copy($path, $copy);
        $this->tmp[] = $copy;

        $service = new BeneficiaryImportService();
        $analysis = $service->analyze($service->readSpreadsheet($copy), 1);
        $this->assertSame(2, $analysis['counts']['ok']);
        $this->assertTrue($analysis['header_detected']);
    }
}
