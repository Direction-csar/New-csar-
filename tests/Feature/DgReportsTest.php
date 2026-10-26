<?php

namespace Tests\Feature;

use App\Models\Personnel;
use App\Models\PublicRequest;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Rapports DG : la page doit s'ouvrir sans redirection, et la génération /
 * l'export doivent produire de vrais fichiers contenant les données réelles.
 */
class DgReportsTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        $user = User::create([
            'name' => 'Test ' . $role,
            'email' => $role . substr(md5(uniqid()), 0, 6) . '@csar.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $user->role = $role;
        $user->two_factor_enabled = true;
        $user->two_factor_secret = 'JBSWY3DPEHPK3PXP';
        $user->save();

        return $user->refresh();
    }

    private function makeRequest(array $overrides = []): PublicRequest
    {
        return PublicRequest::create(array_merge([
            'tracking_code' => 'CSAR-RPT' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'type' => 'aide_alimentaire',
            'status' => 'pending',
            'workflow_status' => 'scannee',
            'full_name' => 'Demandeur Rapport',
            'phone' => '770000000',
            'email' => 'rapport@test.sn',
            'region' => 'Dakar',
            'address' => 'Rue du rapport',
            'description' => 'Description rapport',
            'request_date' => now()->toDateString(),
            'subject' => 'Objet unique rapport XYZ',
            'urgency' => 'haute',
        ], $overrides));
    }

    private function actingAsDg(): User
    {
        $dg = $this->makeUser('dg');
        $this->actingAs($dg, 'dg');

        return $dg;
    }

    public function test_reports_index_loads_without_redirect(): void
    {
        $this->actingAsDg();
        $this->makeRequest();

        $this->get(route('dg.reports.index'))->assertOk();
    }

    public function test_pdf_generation_returns_a_real_pdf_attachment_with_data(): void
    {
        $this->actingAsDg();
        $demande = $this->makeRequest();

        $response = $this->get(route('dg.reports.generate', ['report_type' => 'requests', 'format' => 'pdf']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertMatchesRegularExpression(
            '/^attachment; filename="csar-dg-requests-\d{8}-\d{6}-[a-z0-9]{6}\.pdf"$/',
            $response->headers->get('Content-Disposition')
        );
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertNotEmpty($demande->tracking_code);
    }

    public function test_csv_generation_contains_header_and_real_rows(): void
    {
        $this->actingAsDg();
        $demande = $this->makeRequest();

        $response = $this->get(route('dg.reports.generate', ['report_type' => 'requests', 'format' => 'csv']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertMatchesRegularExpression(
            '/^attachment; filename="csar-dg-requests-\d{8}-\d{6}-[a-z0-9]{6}\.csv"$/',
            $response->headers->get('Content-Disposition')
        );
        $content = $response->getContent();
        $this->assertStringContainsString('Code de suivi', $content);
        $this->assertStringContainsString($demande->tracking_code, $content);
        $this->assertStringContainsString('Objet unique rapport XYZ', $content);
    }

    public function test_generated_report_is_stored_in_private_directory_and_downloadable_by_dg(): void
    {
        $this->actingAsDg();
        $this->makeRequest();

        $response = $this->get(route('dg.reports.generate', ['report_type' => 'requests', 'format' => 'csv']));
        preg_match('/filename="([^"]+)"/', $response->headers->get('Content-Disposition'), $m);
        $path = storage_path('app/reports/' . $m[1]);

        try {
            $this->assertFileExists($path);
            $this->get(route('dg.reports.download', ['filename' => $m[1]]))->assertOk();
            $this->assertFileDoesNotExist(public_path('rapport/' . $m[1]));
        } finally {
            @unlink($path);
        }
    }

    public function test_generated_reports_older_than_retention_are_purged(): void
    {
        $this->actingAsDg();
        $dir = storage_path('app/reports');
        @mkdir($dir, 0700, true);
        $old = $dir . DIRECTORY_SEPARATOR . 'csar-dg-requests-20200101-000000-oldone.csv';
        file_put_contents($old, 'old');
        touch($old, now()->subDays(60)->getTimestamp());

        try {
            $this->get(route('dg.reports.generate', ['report_type' => 'requests', 'format' => 'csv']))->assertOk();
            $this->assertFileDoesNotExist($old);
        } finally {
            @unlink($old);
        }
    }

    public function test_export_csv_works_for_each_supported_type(): void
    {
        $this->actingAsDg();
        Warehouse::create(['name' => 'Entrepot Rapport', 'region' => 'Dakar', 'city' => 'Dakar', 'address' => 'x', 'is_active' => true, 'capacity' => 50, 'latitude' => 14.7, 'longitude' => -17.4]);

        foreach (['requests', 'warehouses', 'users', 'personnel', 'stocks'] as $type) {
            $response = $this->get(route('dg.reports.export', ['type' => $type, 'format' => 'csv']));
            $response->assertOk();
            $this->assertStringStartsWith("\xEF\xBB\xBF", $response->streamedContent(), $type);
        }

        $warehouses = $this->get(route('dg.reports.export', ['type' => 'warehouses']))->streamedContent();
        $this->assertStringContainsString('Entrepot Rapport', $warehouses);
    }

    public function test_exports_never_contain_secrets_or_sensitive_identity_fields(): void
    {
        $dg = $this->actingAsDg();
        $this->makeUser('admin');
        Personnel::create([
            'prenoms_nom' => 'Agent Rapport', 'date_naissance' => '1990-01-01', 'lieu_naissance' => 'Dakar',
            'tranche_age' => '26-35', 'nationalite' => 'Senegalaise', 'numero_cni' => 'CNI-SECRET-999',
            'sexe' => 'Masculin', 'situation_matrimoniale' => 'Celibataire', 'contact_telephonique' => '770000002',
            'email' => 'agent@csar.test', 'groupe_sanguin' => 'O+', 'adresse_complete' => 'Dakar',
            'matricule' => 'MAT-RPT-1', 'date_recrutement_csar' => '2020-01-01', 'date_prise_service_csar' => '2020-02-01',
            'statut' => 'Fonctionnaire', 'poste_actuel' => 'Agent', 'direction_service' => 'DRH',
            'diplome_academique' => 'Licence', 'interet_nouvelles_responsabilites' => 'Oui', 'taille_vetements' => 'M',
            'contact_urgence_nom' => 'Urgence', 'contact_urgence_telephone' => '770000003',
            'contact_urgence_lien_parente' => 'Frere', 'statut_validation' => 'Valide',
        ]);

        $personnel = $this->get(route('dg.reports.export', ['type' => 'personnel']))->streamedContent();
        $users = $this->get(route('dg.reports.export', ['type' => 'users']))->streamedContent();

        $this->assertStringContainsString('MAT-RPT-1', $personnel);
        $this->assertStringNotContainsString('CNI-SECRET-999', $personnel);
        $this->assertStringNotContainsString($dg->password, $users);
        $this->assertStringNotContainsString('JBSWY3DPEHPK3PXP', $users);
        $this->assertStringContainsString($dg->email, $users);
    }

    public function test_unknown_type_or_format_is_refused_instead_of_faking_success(): void
    {
        $this->actingAsDg();

        $this->get(route('dg.reports.generate', ['report_type' => 'inconnu', 'format' => 'pdf']))
            ->assertSessionHasErrors('report_type');
        $this->get(route('dg.reports.generate', ['report_type' => 'requests', 'format' => 'excel']))
            ->assertSessionHasErrors('format');
        $this->get(route('dg.reports.export', ['type' => 'requests', 'format' => 'pdf']))
            ->assertSessionHasErrors('format');
    }

    public function test_reports_are_denied_to_non_dg_roles_and_guests(): void
    {
        $this->get(route('dg.reports.generate', ['report_type' => 'requests', 'format' => 'pdf']))
            ->assertRedirect(route('dg.login'));

        $admin = $this->makeUser('admin');
        $this->actingAs($admin, 'dg')
            ->get(route('dg.reports.export', ['type' => 'requests']))
            ->assertRedirect('/');
    }
}
