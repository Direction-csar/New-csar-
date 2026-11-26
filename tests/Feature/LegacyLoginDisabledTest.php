<?php

namespace Tests\Feature;

use Tests\TestCase;

class LegacyLoginDisabledTest extends TestCase
{
    public function test_legacy_login_routes_are_not_exposed(): void
    {
        foreach (['/simple-login', '/universal-login'] as $uri) {
            $this->get($uri)->assertNotFound();
            $this->post($uri, ['email' => 'a@b.c', 'password' => 'x'])->assertStatus(404);
        }
    }
}
