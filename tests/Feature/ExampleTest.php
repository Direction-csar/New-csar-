<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test basique : la racine redirige vers la locale par défaut.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        // / redirige vers /fr (route locale par défaut)
        $response->assertStatus(302);
    }
}
