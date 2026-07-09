<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LobienLegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_policy_page_is_accessible_with_lobien_content(): void
    {
        $this->seed();

        $response = $this->get('/privacy-policy');

        $response->assertStatus(200);
        $response->assertSee('Information We Collect', false);
        $response->assertSee('Data Security', false);
        $response->assertSee('Cookies', false);
        $response->assertSee('Philippines 1630', false);
    }

    public function test_terms_of_use_page_is_accessible_with_lobien_content(): void
    {
        $this->seed();

        $response = $this->get('/terms-of-use');

        $response->assertStatus(200);
        $response->assertSee('Intellectual Property', false);
        $response->assertSee('Governing Law', false);
        $response->assertSee('Taguig City', false);
    }

    public function test_legacy_terms_of_service_url_redirects_to_terms_of_use(): void
    {
        $this->seed();

        $this->get('/terms-of-service')
            ->assertRedirect('/terms-of-use');
    }
}
