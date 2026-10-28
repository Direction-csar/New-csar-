<?php

namespace Tests\Feature;

use App\Models\Archive;
use App\Models\ArchiveFolder;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Audit navigation / cloisonnement des portails de direction :
 * chaque rôle reste dans son espace, les archives sont cloisonnées,
 * et les routes partagées (dossiers) vérifient la direction côté serveur.
 * Schéma minimal SQLite :memory:.
 */
class DirectionPortalAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
    }

    private function makeUser(string $role, ?string $department = null): User
    {
        $user = User::create([
            'name' => 'Portal ' . $role,
            'email' => $role . substr(md5(uniqid()), 0, 6) . '@csar.test',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $user->role = $role;
        $user->department = $department;
        $user->save();
        return $user->refresh();
    }

    private function createSchema(): void
    {
        foreach (['archive_access_logs', 'archives', 'archive_folders', 'public_requests', 'users'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password')->nullable();
            $t->string('role')->default('agent');
            $t->string('department')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamp('last_activity')->nullable();
            $t->rememberToken();
            $t->timestamps();
        });

        Schema::create('archive_folders', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('direction');
            $t->unsignedBigInteger('parent_id')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        Schema::create('archives', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->nullable();
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('direction');
            $t->unsignedBigInteger('folder_id')->nullable();
            $t->integer('annee');
            $t->string('file_path');
            $t->string('file_name');
            $t->unsignedBigInteger('file_size')->default(0);
            $t->string('mime_type')->nullable();
            $t->integer('page_count')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('deleted_by')->nullable();
            $t->softDeletes();
            $t->timestamps();
        });

        Schema::create('archive_access_logs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('archive_id');
            $t->unsignedBigInteger('user_id')->nullable();
            $t->string('action');
            $t->string('ip_address')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();
        });

        // Le layout admin compte les demandes par workflow_status dans la sidebar.
        Schema::create('public_requests', function (Blueprint $t) {
            $t->id();
            $t->string('workflow_status')->nullable();
            $t->timestamps();
        });
    }

    // ===== Accès par portail : chacun dans son espace =====

    public function test_dtl_user_sees_direction_portal_layout(): void
    {
        $dtl = $this->makeUser('dtl');

        $response = $this->actingAs($dtl, 'dtl')->get('/archives/dtl');

        $response->assertOk();
        $this->assertSame('layouts.direction-portal', $response->viewData('layout'));
    }

    public function test_dtl_user_with_admin_session_stays_in_dtl_portal(): void
    {
        // Régression : avec une session admin active dans le même navigateur,
        // l'utilisateur connecté via le guard DTL doit rester dans l'espace DTL.
        $dtl = $this->makeUser('dtl');
        $admin = $this->makeUser('admin');

        $this->actingAs($admin, 'admin');
        $this->actingAs($dtl, 'dtl');

        $response = $this->get('/archives/dtl');

        $response->assertOk();
        $this->assertSame('layouts.direction-portal', $response->viewData('layout'));
    }

    public function test_admin_only_session_gets_admin_layout_on_direction_archives(): void
    {
        $admin = $this->makeUser('admin');

        $response = $this->actingAs($admin, 'admin')->get('/archives/dtl');

        $response->assertOk();
        $this->assertSame('layouts.admin', $response->viewData('layout'));
    }

    public function test_inspecteur_archives_route_exists_and_loads(): void
    {
        $this->assertTrue(Route::has('archives.inspecteur.index'), 'La route archives.inspecteur.index doit exister');

        $inspecteur = $this->makeUser('inspecteur');
        $this->actingAs($inspecteur, 'inspecteur')->get('/archives/inspecteur')->assertOk();
    }

    public function test_comptable_archives_load(): void
    {
        $comptable = $this->makeUser('comptable');
        $this->actingAs($comptable, 'comptable')->get('/archives/comptable')->assertOk();
    }

    // ===== Refus d'accès : URL directe, mauvais rôle, non authentifié =====

    public function test_dtl_user_cannot_access_drh_archives(): void
    {
        $dtl = $this->makeUser('dtl');

        $response = $this->actingAs($dtl, 'dtl')->get('/archives/drh');

        $response->assertRedirect(route('drh.login'));
    }

    public function test_dtl_user_cannot_access_cpm_archives(): void
    {
        $dtl = $this->makeUser('dtl');

        $response = $this->actingAs($dtl, 'dtl')->get('/archives/cpm');

        $response->assertRedirect(route('cpm.login'));
    }

    public function test_guest_is_redirected_to_direction_login(): void
    {
        $this->get('/archives/dtl')->assertRedirect(route('dtl.login'));
        $this->get('/archives/comptable')->assertRedirect(route('comptable.login'));
    }

    public function test_direction_user_cannot_view_other_direction_archive(): void
    {
        $dtl = $this->makeUser('dtl');
        $drhArchive = Archive::create([
            'title' => 'Doc DRH',
            'direction' => 'DRH',
            'annee' => 2026,
            'file_path' => 'archives/drh/x.pdf',
            'file_name' => 'x.pdf',
            'file_size' => 100,
            'mime_type' => 'application/pdf',
        ]);

        // La route /archives/dtl/{id} avec un document DRH doit être refusée.
        $response = $this->actingAs($dtl, 'dtl')->get("/archives/dtl/{$drhArchive->id}");
        $response->assertForbidden();
    }

    // ===== Dossiers partagés : contrôle serveur de la direction =====

    public function test_dtl_user_can_create_folder_in_dtl(): void
    {
        $dtl = $this->makeUser('dtl');

        $response = $this->actingAs($dtl, 'dtl')->post('/archive-folders', [
            'name' => 'Dossier DTL',
            'direction' => 'DTL',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('archive_folders', ['name' => 'Dossier DTL', 'direction' => 'DTL']);
    }

    public function test_dtl_user_cannot_create_folder_in_drh(): void
    {
        $dtl = $this->makeUser('dtl');

        $response = $this->actingAs($dtl, 'dtl')->post('/archive-folders', [
            'name' => 'Intrusion DRH',
            'direction' => 'DRH',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('archive_folders', ['name' => 'Intrusion DRH']);
    }

    public function test_folder_creation_is_not_redirected_to_admin_login(): void
    {
        // Régression : le POST /archive-folders ne doit plus passer par le
        // middleware admin qui renvoyait les utilisateurs DTL vers /admin/login.
        $dtl = $this->makeUser('dtl');

        $response = $this->actingAs($dtl, 'dtl')->post('/archive-folders', [
            'name' => 'Suivi logistique',
            'direction' => 'DTL',
        ]);

        $response->assertSessionMissing('errors');
        $this->assertStringNotContainsString('/admin/login', $response->headers->get('Location') ?? '');
    }

    public function test_admin_can_create_folder_in_any_direction(): void
    {
        $admin = $this->makeUser('admin');

        $response = $this->actingAs($admin, 'admin')->post('/archive-folders', [
            'name' => 'Dossier admin DFC',
            'direction' => 'DFC',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('archive_folders', ['name' => 'Dossier admin DFC', 'direction' => 'DFC']);
    }

    public function test_guest_cannot_create_folder(): void
    {
        $response = $this->post('/archive-folders', [
            'name' => 'Anonyme',
            'direction' => 'DTL',
        ]);

        $this->assertDatabaseMissing('archive_folders', ['name' => 'Anonyme']);
    }

    // ===== Upload : la policy autorise le rôle de la direction =====

    public function test_comptable_user_can_upload_to_own_archives(): void
    {
        $comptable = $this->makeUser('comptable');

        $file = \Illuminate\Http\UploadedFile::fake()->create('justificatif.pdf', 50, 'application/pdf');

        $response = $this->actingAs($comptable, 'comptable')->post('/archives/comptable', [
            'title' => 'Justificatif stock',
            'annee' => 2026,
            'file' => $file,
        ]);

        $response->assertSessionMissing('errors');
        $this->assertDatabaseHas('archives', ['title' => 'Justificatif stock', 'direction' => 'COMPTABLE']);
    }
}
