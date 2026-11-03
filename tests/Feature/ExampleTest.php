<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_api_blocks_requests_without_key(): void
    {
        $response = $this->postJson('/api/login', []);
        $response->assertStatus(401);
    }

    public function test_api_allows_requests_with_key(): void
    {
        $response = $this->withHeaders([
            'X-API-KEY' => config('app.api_key'),
        ])->postJson('/api/login', []);

        // Con clave pasa el middleware de ApiKey (devuelve 422 por validación de email/password, no 401)
        $response->assertStatus(422);
    }
}
