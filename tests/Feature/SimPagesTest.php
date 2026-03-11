<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SimPagesTest extends TestCase
{
    use RefreshDatabase;

    /** Dashboard SIM public (sim-reports) retourne 200 */
    public function test_sim_reports_page_returns_200(): void
    {
        $response = $this->get('/sim-reports');
        $response->assertStatus(200);
        $response->assertSee('SIM', false);
    }

    /** Consultation des prix (avec locale) retourne 200 */
    public function test_sim_consultation_prix_page_returns_200(): void
    {
        $response = $this->get('/fr/sim/consultation-prix');
        $response->assertStatus(200);
        $response->assertSee('CONSULTATION', false);
    }

    /** Carte des marchés retourne 200 */
    public function test_sim_carte_marches_page_returns_200(): void
    {
        $response = $this->get('/fr/sim/carte-marches');
        $response->assertStatus(200);
        $response->assertSee('MARCHÉS', false);
    }
}
