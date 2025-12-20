<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\News;
use App\Models\Warehouse;
use App\Models\Speech;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que la page d'accueil est accessible
     */
    public function test_home_page_accessible()
    {
        $response = $this->get('/fr');
        
        $response->assertStatus(200);
        $response->assertSee('CSAR');
    }

    /**
     * Test de la page À propos
     */
    public function test_about_page_accessible()
    {
        $response = $this->get('/fr/a-propos');
        
        $response->assertStatus(200);
        $response->assertSee('À propos');
    }

    /**
     * Test de la page Institution
     */
    public function test_institution_page_accessible()
    {
        $response = $this->get('/fr/institution');
        
        $response->assertStatus(200);
    }

    /**
     * Test de la page Actualités
     */
    public function test_actualites_page_accessible()
    {
        $response = $this->get('/fr/actualites');
        
        $response->assertStatus(200);
    }

    /**
     * Test de la page Galerie
     */
    public function test_galerie_page_accessible()
    {
        $response = $this->get('/fr/galerie');
        
        $response->assertStatus(200);
    }

    /**
     * Test de la page Contact
     */
    public function test_contact_page_accessible()
    {
        $response = $this->get('/fr/contact');
        
        $response->assertStatus(200);
        $response->assertSee('Contact');
    }

    /**
     * Test de la carte interactive
     */
    public function test_map_page_accessible()
    {
        $response = $this->get('/fr/carte-interactive');
        
        $response->assertStatus(200);
    }

    /**
     * Test de la page Mentions Légales
     */
    public function test_legal_notice_page_accessible()
    {
        $response = $this->get('/fr/mentions-legales');
        
        $response->assertStatus(200);
        $response->assertSee('Mentions Légales');
    }

    /**
     * Test que le sitemap.xml est généré
     */
    public function test_sitemap_generated()
    {
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('<?xml');
        $response->assertSee('urlset');
    }

    /**
     * Test que robots.txt existe
     */
    public function test_robots_txt_exists()
    {
        $response = $this->get('/robots.txt');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('User-agent');
        $response->assertSee('Disallow: /admin/');
    }

    /**
     * Test de la redirection FR par défaut
     */
    public function test_root_redirects_to_fr()
    {
        $response = $this->get('/');
        
        $response->assertRedirect('/fr');
    }

    /**
     * Test que les pages admin sont protégées du public
     */
    public function test_admin_pages_not_accessible_publicly()
    {
        $response = $this->get('/admin');
        
        $response->assertRedirect(); // Doit rediriger vers login
    }

    /**
     * Test de la page EN (anglais)
     */
    public function test_english_version_accessible()
    {
        $response = $this->get('/en');
        
        $response->assertStatus(200);
    }

    /**
     * Test du détail d'une actualité
     */
    public function test_news_detail_page_shows_article()
    {
        $news = News::factory()->create([
            'title' => 'Test Article',
            'content' => 'Contenu de test',
            'status' => 'published',
            'published_at' => now()
        ]);

        $response = $this->get('/fr/actualites/' . $news->id);

        $response->assertStatus(200);
        $response->assertSee('Test Article');
    }

    /**
     * Test que la carte affiche les entrepôts
     */
    public function test_map_shows_warehouses()
    {
        Warehouse::factory()->create([
            'name' => 'Entrepôt Test',
            'latitude' => 14.7167,
            'longitude' => -17.4677,
            'is_active' => true
        ]);

        $response = $this->get('/fr/carte-interactive');

        $response->assertStatus(200);
        $response->assertSee('Entrepôt Test');
    }
}






































