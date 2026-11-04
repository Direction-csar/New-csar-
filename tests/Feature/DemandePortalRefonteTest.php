<?php

namespace Tests\Feature;

use App\Models\PublicRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemandePortalRefonteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Queue::fake();
        Storage::fake('local');
    }

    private function payload(array $changes = []): array
    {
        return array_replace([
            'nom' => 'Fictif', 'prenom' => 'Demandeur', 'demandeur_type' => 'particulier',
            'email' => 'demandeur@example.test', 'telephone' => '77 123 45 67',
            'type_demande' => 'aide_alimentaire', 'objet' => 'Objet fictif',
            'description' => 'Description fictive suffisamment longue.',
            'region' => 'Dakar', 'departement' => 'Dakar', 'commune' => 'Medina',
            'adresse' => 'Rue fictive', 'exactitude' => '1',
        ], $changes);
    }

    public function test_selection_page_offers_both_modes(): void
    {
        $this->get('/demande')->assertOk()
            ->assertSee('Remplir une demande en ligne')
            ->assertSee('lettre de demande');
        $this->get('/demande?mode=formulaire')->assertOk()->assertSee('Remplir une demande en ligne');
        $this->get('/demande?mode=lettre')->assertOk()->assertSee('lettre');
    }

    public function test_particulier_with_cni_and_organisation_with_ninea(): void
    {
        $this->postJson('/demande', $this->payload(['cni' => '1234567890123']))
            ->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseHas('public_requests', [
            'demandeur_type' => 'particulier', 'cni' => '1234567890123', 'ninea' => null,
        ]);

        $this->postJson('/demande', $this->payload([
            'email' => 'org@example.test', 'telephone' => '77 999 88 77',
            'demandeur_type' => 'entreprise', 'structure_name' => 'SARL Fictive',
            'ninea' => '005123456A', 'cni' => '9999999',
        ]))->assertOk();
        $this->assertDatabaseHas('public_requests', [
            'demandeur_type' => 'entreprise', 'structure_name' => 'SARL Fictive',
            'ninea' => '005123456A', 'cni' => null,
        ]);
    }

    public function test_all_workflow_natures_and_event_fields(): void
    {
        // « Nature de la demande » = catégories de workflow existantes (4 valeurs)
        foreach (['aide_alimentaire', 'demande_audience', 'information_generale', 'autre'] as $nature) {
            $this->postJson('/demande', $this->payload([
                'email' => 'n' . md5($nature) . '@example.test', 'telephone' => '77 ' . sprintf('%07d', crc32($nature) % 10000000),
                'objet' => 'Objet ' . $nature, 'description' => 'Desc ' . $nature,
                'type_demande' => $nature, 'type_demande_autre' => 'Autre précisé',
            ]))->assertOk();
        }

        // Profil indépendant de la nature : une organisation religieuse peut demander une aide alimentaire
        $this->postJson('/demande', $this->payload([
            'email' => 'evt@example.test', 'telephone' => '78 555 44 22',
            'objet' => 'Cérémonie fictive', 'type_demande' => 'aide_alimentaire',
            'demandeur_type' => 'organisation_religieuse', 'structure_name' => 'Dahira Test',
            'event_date' => now()->addDays(10)->toDateString(),
            'event_location' => 'Grande mosquée', 'event_participants' => 500,
        ]))->assertOk();
        $this->assertDatabaseHas('public_requests', [
            'type' => 'aide_alimentaire', 'demandeur_type' => 'organisation_religieuse',
            'event_participants' => 500, 'structure_name' => 'Dahira Test',
        ]);

        // Date passée → refusée
        $this->postJson('/demande', $this->payload([
            'email' => 'past@example.test', 'event_date' => '2020-01-01',
        ]))->assertUnprocessable()->assertJsonValidationErrors('event_date');
    }

    public function test_structure_required_for_organisations_but_ninea_never(): void
    {
        $this->postJson('/demande', $this->payload(['demandeur_type' => 'association']))
            ->assertUnprocessable()->assertJsonValidationErrors('structure_name');
        // NINEA vide → accepté quand même
        $this->postJson('/demande', $this->payload([
            'demandeur_type' => 'organisation_religieuse', 'structure_name' => 'Mosquée Fictive',
            'ninea' => null,
        ]))->assertOk();
    }

    public function test_invalid_cni_and_ninea_formats_are_rejected(): void
    {
        $this->postJson('/demande', $this->payload(['cni' => 'ABC-DEF']))
            ->assertUnprocessable()->assertJsonValidationErrors('cni');
        $this->postJson('/demande', $this->payload(['demandeur_type' => 'organisation', 'ninea' => '@@!!']))
            ->assertUnprocessable()->assertJsonValidationErrors('ninea');
    }

    public function test_senegal_phone_validation(): void
    {
        $this->postJson('/demande', $this->payload(['telephone' => '+33612345678', 'email' => 'a@b.test']))
            ->assertUnprocessable()->assertJsonValidationErrors('telephone');
        foreach (['77 600 97 60', '70 123 45 67', '76 999 00 11', '78 111 22 33', '75 555 44 33', '33 824 00 00'] as $tel) {
            $this->postJson('/demande', $this->payload([
                'telephone' => $tel, 'email' => 'tel' . md5($tel) . '@example.test',
                'objet' => 'Objet ' . $tel, 'description' => 'Desc ' . $tel,
            ]))->assertOk();
        }
    }

    public function test_unknown_region_is_rejected_and_departement_optional(): void
    {
        $this->postJson('/demande', $this->payload(['region' => 'RegionInventee']))
            ->assertUnprocessable()->assertJsonValidationErrors('region');
        $this->postJson('/demande', $this->payload(['departement' => null, 'commune' => null, 'adresse' => null]))
            ->assertOk();
    }

    public function test_location_required_gps_or_region_fallback(): void
    {
        $noLoc = ['latitude' => null, 'longitude' => null, 'region' => null, 'departement' => null];

        // Ni GPS ni région → bloqué côté serveur avec message explicite
        $this->postJson('/demande', $this->payload($noLoc))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude' => 'Capturez votre position actuelle']);

        // Coordonnée partielle → refusée (aucune fausse position enregistrée)
        $this->postJson('/demande', $this->payload(array_merge($noLoc, ['latitude' => '14.7'])))
            ->assertUnprocessable()->assertJsonValidationErrors('longitude');

        // GPS seul (cas nominal : aucun champ d'adresse) → accepté et lié au dossier
        $this->postJson('/demande', $this->payload(array_merge($noLoc, [
            'latitude' => '14.716700', 'longitude' => '-17.467700',
        ])))->assertOk();
        $d = PublicRequest::sole();
        $this->assertEquals(14.7167, (float) $d->latitude);
        $this->assertEquals(-17.4677, (float) $d->longitude);
        $this->assertSame('', (string) $d->region);

        // Repli : capture impossible → région seule acceptée
        $this->postJson('/demande', $this->payload(array_merge($noLoc, [
            'email' => 'repli@example.test', 'telephone' => '70 222 33 44', 'objet' => 'Repli région',
            'region' => 'Kolda',
        ])))->assertOk();
        $this->assertDatabaseHas('public_requests', ['email' => 'repli@example.test', 'region' => 'Kolda', 'latitude' => null]);

        // Coordonnées hors bornes → refusées
        $this->postJson('/demande', $this->payload(array_merge($noLoc, ['latitude' => '120', 'longitude' => '-17'])))
            ->assertUnprocessable()->assertJsonValidationErrors('latitude');
    }

    public function test_phone_normalized_with_or_without_prefix(): void
    {
        $cases = [
            '77 123 45 01'       => '+221771234501',
            '771234502'          => '+221771234502',
            '+221 77 123 45 03'  => '+221771234503',
            '00221771234504'     => '+221771234504',
            '221 77 123 45 05'   => '+221771234505',
            '+221 221 771234506' => '+221771234506',
        ];
        $i = 0;
        foreach ($cases as $input => $expected) {
            $this->postJson('/demande', $this->payload([
                'telephone' => $input, 'email' => 'tel' . (++$i) . '@example.test', 'objet' => 'Tel ' . $i,
            ]))->assertOk();
            $this->assertDatabaseHas('public_requests', ['email' => 'tel' . $i . '@example.test', 'phone' => $expected]);
        }
        foreach (['', '12345', '+33 6 12 34 56 78', '79 123 45 67'] as $bad) {
            $this->postJson('/demande', $this->payload(['telephone' => $bad, 'email' => 'bad@example.test']))
                ->assertUnprocessable()->assertJsonValidationErrors('telephone');
        }
    }

    public function test_coordinates_never_exposed_publicly(): void
    {
        $this->postJson('/demande', $this->payload(['latitude' => '14.123456', 'longitude' => '-16.654321']))->assertOk();
        $d = PublicRequest::sole();
        $summary = (array) $d->publicSummary();
        $this->assertArrayNotHasKey('latitude', $summary);
        $this->assertArrayNotHasKey('longitude', $summary);
        $pdf = view('public.pdf.request', ['request' => $d->publicSummary()])->render();
        $this->assertStringNotContainsString('14.123456', $pdf);
        $this->assertStringNotContainsString('16.654321', $pdf);
    }

    public function test_admin_sees_coordinates_but_anonymous_cannot(): void
    {
        $this->postJson('/demande', $this->payload(['latitude' => '14.123456', 'longitude' => '-16.654321']))->assertOk();
        $d = PublicRequest::sole();
        $this->get("/admin/demandes/{$d->id}/edit")->assertRedirect();

        $admin = new User();
        $admin->forceFill([
            'name' => 'Admin Geo', 'email' => uniqid() . '@example.test', 'password' => bcrypt('fixture'),
            'role' => 'admin', 'is_active' => true,
            'two_factor_enabled' => true, 'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
        ]);
        $admin->save();
        $this->actingAs($admin, 'admin')->get("/admin/demandes/{$d->id}/edit")
            ->assertOk()->assertSee('14.123456')->assertSee('Voir sur la carte');
    }

    public function test_lettre_accepts_optional_attachments_and_gps(): void
    {
        $this->post('/demande/lettre', $this->payload([
            'lettre' => UploadedFile::fake()->create('lettre.docx', 60, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            'justificatifs' => [UploadedFile::fake()->create('annexe.pdf', 40, 'application/pdf')],
            'region' => null, 'departement' => null, 'latitude' => '14.8', 'longitude' => '-17.2',
        ]))->assertRedirect()->assertSessionHasNoErrors();
        $d = PublicRequest::sole();
        $this->assertSame('lettre', $d->depot_mode);
        $this->assertStringStartsWith('demandes/lettres/', $d->letter_path);
        $this->assertCount(1, $d->attachments);
        Storage::disk('local')->assertExists($d->attachments[0]);
        $this->assertEquals(14.8, (float) $d->latitude);

        // Sans aucune localisation → refus
        $this->post('/demande/lettre', $this->payload([
            'email' => 'l2@example.test', 'objet' => 'Lettre 2',
            'lettre' => UploadedFile::fake()->create('l2.pdf', 30, 'application/pdf'),
            'region' => null, 'departement' => null,
        ]))->assertSessionHasErrors('latitude');
    }

    public function test_form_markup_single_prefix_single_banner_and_geo_button(): void
    {
        foreach (['/fr/demande?mode=formulaire', '/fr/demande?mode=lettre'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertSame(1, substr_count($html, 'class="dm-tel-prefix"'), $url);
            $this->assertStringNotContainsString('alt="SN"', $html);
            $this->assertStringNotContainsString('🇸🇳', $html);
            $this->assertStringContainsString('Capturer ma position actuelle', $html);
            foreach (['id="departement"', 'id="commune"', 'id="adresse"', 'leaflet'] as $gone) {
                $this->assertStringNotContainsString($gone, $html, "$url contient encore $gone");
            }
            $this->assertStringNotContainsString('type="number" name="latitude"', $html);
            $this->assertDoesNotMatchRegularExpression('/Ã©|Ã¨|â€™/', $html);
        }
        $form = $this->get('/fr/demande?mode=formulaire')->getContent();
        $this->assertSame(1, substr_count($form, 'ne garantit pas'));
        $this->assertSame(1, substr_count($form, 'class="dm-steps"'));
        $this->assertStringContainsString('Documents complémentaires', $form);
    }

    public function test_lettre_mode_stores_private_file_and_metadata(): void
    {
        $file = UploadedFile::fake()->create('ma-lettre.pdf', 120, 'application/pdf');
        $this->post('/demande/lettre', $this->payload(['lettre' => $file]) + ['description' => null])
            ->assertRedirect();

        $d = PublicRequest::sole();
        $this->assertSame('lettre', $d->depot_mode);
        $this->assertNotNull($d->letter_path);
        $this->assertStringStartsWith('demandes/lettres/', $d->letter_path);
        Storage::disk('local')->assertExists($d->letter_path);
        // Le nom d'origine n'est pas conservé (nom aléatoire sécurisé).
        $this->assertStringNotContainsString('ma-lettre', $d->letter_path);
    }

    public function test_lettre_rejects_forbidden_mime_and_missing_file(): void
    {
        $exe = UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload');
        $this->post('/demande/lettre', $this->payload(['lettre' => $exe]))
            ->assertSessionHasErrors('lettre');
        $this->post('/demande/lettre', $this->payload())
            ->assertSessionHasErrors('lettre');
        $this->assertDatabaseCount('public_requests', 0);
    }

    public function test_justificatifs_are_stored_privately_and_listed(): void
    {
        $files = [
            UploadedFile::fake()->create('doc1.pdf', 50, 'application/pdf'),
            UploadedFile::fake()->image('photo.jpg'),
        ];
        $this->postJson('/demande', $this->payload(['justificatifs' => $files]))->assertOk();
        $d = PublicRequest::sole();
        $this->assertCount(2, $d->attachments);
        foreach ($d->attachments as $path) {
            $this->assertStringStartsWith('demandes/justificatifs/', $path);
            Storage::disk('local')->assertExists($path);
        }
    }

    public function test_forbidden_attachment_mime_is_rejected(): void
    {
        $files = [UploadedFile::fake()->create('script.sh', 5, 'application/x-sh')];
        $this->postJson('/demande', $this->payload(['justificatifs' => $files]))
            ->assertUnprocessable()->assertJsonValidationErrors('justificatifs');
    }

    public function test_private_download_requires_admin_and_serves_authorized_agent(): void
    {
        $file = UploadedFile::fake()->create('lettre.pdf', 80, 'application/pdf');
        $this->post('/demande/lettre', $this->payload(['lettre' => $file]));
        $d = PublicRequest::sole();

        // Non connecté → refus
        $this->get("/admin/demandes/{$d->id}/lettre")->assertRedirect();
        $this->get("/admin/demandes/{$d->id}/piece/0")->assertRedirect();

        // Agent admin → OK (garde 'admin', 2FA activée pour franchir enforce.2fa)
        $admin = new User();
        $admin->forceFill([
            'name' => 'Admin Test', 'email' => uniqid() . '@example.test',
            'password' => bcrypt('fixture'), 'role' => 'admin', 'is_active' => true,
            'two_factor_enabled' => true, 'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
        ]);
        $admin->save();
        $this->actingAs($admin, 'admin')->get("/admin/demandes/{$d->id}/lettre")
            ->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');

        // Chemin inexistant → 404
        $this->actingAs($admin, 'admin')->get("/admin/demandes/{$d->id}/piece/9")->assertNotFound();
    }

    public function test_region_fallback_without_gps_and_gps_saved(): void
    {
        // Sans GPS mais avec région de repli → accepté
        $this->postJson('/demande', $this->payload(['latitude' => null, 'longitude' => null]))->assertOk();
        $d = PublicRequest::sole();
        $this->assertNull($d->latitude);

        // Avec GPS → enregistré (payload distinct pour éviter l'anti-doublon)
        $this->postJson('/demande', $this->payload([
            'email' => 'gps@example.test', 'telephone' => '76 555 44 33',
            'objet' => 'Objet GPS distinct', 'latitude' => '14.7167', 'longitude' => '-17.4677',
        ]))->assertOk();
        $this->assertDatabaseHas('public_requests', ['email' => 'gps@example.test', 'latitude' => 14.7167]);
    }

    public function test_exactitude_checkbox_is_required(): void
    {
        $this->postJson('/demande', $this->payload(['exactitude' => null]))
            ->assertUnprocessable()->assertJsonValidationErrors('exactitude');
    }

    public function test_tracking_two_step_flow_keeps_security(): void
    {
        $this->postJson('/demande', $this->payload())->assertOk();
        $d = PublicRequest::sole();

        // Étape 1 : référence invalide → erreur générique, pas de fuite
        $this->post('/suivi', ['action' => 'lookup', 'tracking_code' => 'CSAR-INEXISTANT'])
            ->assertRedirect()->assertSessionHas('tracking_error');

        // Étape 1 : référence valide → étape email proposée, rien d'exposé
        $this->post('/suivi', ['action' => 'lookup', 'tracking_code' => $d->tracking_code])
            ->assertRedirect()->assertSessionHas('tracking_verify')
            ->assertSessionMissing('tracking_error');

        // Étape 2 : email → réponse générique (même pour un mauvais email)
        $this->post('/suivi', ['tracking_code' => $d->tracking_code, 'email' => 'wrong@example.test'])
            ->assertRedirect()->assertSessionHas('tracking_message')
            ->assertSessionMissing('tracking_error');

        // Mauvais code → refus ; le dossier reste non consultable sans OTP
        $this->post('/suivi', ['tracking_code' => $d->tracking_code, 'email' => $d->email]);
        $this->post('/suivi', ['action' => 'confirm', 'verification_code' => '000000'])
            ->assertSessionHas('tracking_error');
        $this->get('/suivi?code=' . $d->tracking_code)->assertDontSee($d->subject);
    }

    public function test_cni_never_leaks_in_public_summary(): void
    {
        $this->postJson('/demande', $this->payload(['cni' => '1234567890123']))->assertOk();
        $summary = PublicRequest::sole()->publicSummary();
        $this->assertStringNotContainsString('1234567890123', json_encode($summary));
        $this->assertObjectNotHasProperty('cni', $summary);
    }
}
