<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokenSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_credentials_create_a_sanctum_token(): void
    {
        User::factory()->create([
            'email' => 'operator@example.test',
            'password' => 'correct-password',
        ]);

        $response = $this->postJson('/api/tokens', [
            'email' => 'operator@example.test',
            'password' => 'correct-password',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure(['token', 'token_type'])
            ->assertJsonPath('token_type', 'Bearer');

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_invalid_credentials_use_a_uniform_error(): void
    {
        User::factory()->create([
            'email' => 'operator@example.test',
            'password' => 'correct-password',
        ]);

        $unknownUser = $this->postJson('/api/tokens', [
            'email' => 'unknown@example.test',
            'password' => 'wrong-password',
        ]);

        $wrongPassword = $this->postJson('/api/tokens', [
            'email' => 'operator@example.test',
            'password' => 'wrong-password',
        ]);

        $unknownUser->assertUnprocessable()->assertJsonPath(
            'errors.email.0',
            'The provided credentials are incorrect.',
        );
        $wrongPassword->assertUnprocessable()->assertJsonPath(
            'errors.email.0',
            'The provided credentials are incorrect.',
        );
    }

    public function test_token_creation_is_rate_limited(): void
    {
        $payload = [
            'email' => 'rate-limit@example.test',
            'password' => 'wrong-password',
        ];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/tokens', $payload)->assertUnprocessable();
        }

        $this->postJson('/api/tokens', $payload)->assertTooManyRequests();
    }
}
