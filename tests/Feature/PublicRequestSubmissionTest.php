<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PublicRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PublicRequestSubmissionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que le formulaire de demande peut être soumis avec succès
     */
    public function test_public_request_can_be_submitted_successfully()
    {
        // Données de test valides
        $requestData = [
            'nom' => 'Diallo',
            'prenom' => 'Mamadou',
            'email' => 'mamadou.diallo@test.com',
            'telephone' => '+221701234567',
            'objet' => 'Demande d\'assistance alimentaire',
            'description' => 'Je sollicite votre aide pour ma famille de 5 personnes en situation difficile depuis 2 mois.',
            'type' => 'aide_alimentaire',
            'region' => 'Dakar',
            'latitude' => 14.7167,
            'longitude' => -17.4677,
        ];

        // Soumettre la demande
        $response = $this->post('/demande', $requestData);

        // Vérifier la redirection vers la page de succès
        $response->assertRedirect(route('demande.success'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('tracking_code');

        // Vérifier que la demande a été créée en base de données
        $this->assertDatabaseHas('public_requests', [
            'name' => 'Diallo',
            'full_name' => 'Diallo Mamadou',
            'email' => 'mamadou.diallo@test.com',
            'phone' => '+221701234567',
            'type' => 'aide_alimentaire',
            'status' => 'pending',
            'region' => 'Dakar',
        ]);

        // Vérifier qu'un code de suivi a été généré
        $publicRequest = PublicRequest::where('email', 'mamadou.diallo@test.com')->first();
        $this->assertNotNull($publicRequest);
        $this->assertNotNull($publicRequest->tracking_code);
        $this->assertStringStartsWith('DEM', $publicRequest->tracking_code);
    }

    /**
     * Test que l'événement DemandeCreated est déclenché
     */
    public function test_demande_created_event_is_dispatched()
    {
        \Event::fake();

        $requestData = [
            'nom' => 'Sow',
            'prenom' => 'Aminata',
            'email' => 'aminata.sow@test.com',
            'telephone' => '+221775554433',
            'objet' => 'Aide matérielle',
            'description' => 'Besoin urgent de matériel pour ma famille.',
            'type' => 'aide_materielle',
            'region' => 'Thiès',
        ];

        $response = $this->post('/demande', $requestData);

        // Vérifier que l'événement a été déclenché
        \Event::assertDispatched(\App\Events\DemandeCreated::class);
    }

    /**
     * Test validation des champs obligatoires
     */
    public function test_required_fields_are_validated()
    {
        $response = $this->post('/demande', [
            // Données incomplètes
            'nom' => 'Test',
            // Manque prenom, email, telephone, objet, description
        ]);

        $response->assertSessionHasErrors(['prenom', 'email', 'telephone', 'objet', 'description']);
    }

    /**
     * Test validation du format email
     */
    public function test_email_format_is_validated()
    {
        $requestData = [
            'nom' => 'Diallo',
            'prenom' => 'Mamadou',
            'email' => 'email-invalide', // Email invalide
            'telephone' => '+221701234567',
            'objet' => 'Test',
            'description' => 'Test description avec plus de 10 caractères',
        ];

        $response = $this->post('/demande', $requestData);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test que la longueur maximale de la description est respectée
     */
    public function test_description_max_length_is_validated()
    {
        $requestData = [
            'nom' => 'Diallo',
            'prenom' => 'Mamadou',
            'email' => 'test@test.com',
            'telephone' => '+221701234567',
            'objet' => 'Test',
            'description' => str_repeat('a', 2001), // Plus de 2000 caractères
        ];

        $response = $this->post('/demande', $requestData);

        $response->assertSessionHasErrors('description');
    }

    /**
     * Test que les coordonnées GPS sont correctement enregistrées
     */
    public function test_gps_coordinates_are_saved()
    {
        $requestData = [
            'nom' => 'Diallo',
            'prenom' => 'Mamadou',
            'email' => 'gps@test.com',
            'telephone' => '+221701234567',
            'objet' => 'Test GPS',
            'description' => 'Test avec coordonnées GPS',
            'latitude' => 14.7167,
            'longitude' => -17.4677,
        ];

        $response = $this->post('/demande', $requestData);

        $this->assertDatabaseHas('public_requests', [
            'email' => 'gps@test.com',
            'latitude' => 14.7167,
            'longitude' => -17.4677,
        ]);
    }

    /**
     * Test que l'IP et le User Agent sont enregistrés
     */
    public function test_ip_and_user_agent_are_saved()
    {
        $requestData = [
            'nom' => 'Diallo',
            'prenom' => 'Mamadou',
            'email' => 'ip@test.com',
            'telephone' => '+221701234567',
            'objet' => 'Test',
            'description' => 'Test IP et User Agent',
        ];

        $response = $this->post('/demande', $requestData);

        $publicRequest = PublicRequest::where('email', 'ip@test.com')->first();
        $this->assertNotNull($publicRequest->ip_address);
        $this->assertNotNull($publicRequest->user_agent);
    }
}










































