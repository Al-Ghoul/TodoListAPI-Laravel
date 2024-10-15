<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Register new user with existing email.
     */
    public function test_registering_a_new_account_with_missing_fields_fail(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Sally',
            'password' => 'secret',
        ]);

        $response
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /**
     * Register new user.
     */
    public function test_registering_a_new_account_success(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Sally',
            'email' => 'sally@me.com',
            'password' => 'secret',
            'confirm_password' => 'secret',
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);
    }

    /**
     * Register new user with existing email.
     */
    public function test_registering_a_new_account_with_existing_email_fail(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Sally',
            'email' => 'sally@me.com',
            'password' => 'secret',
            'confirm_password' => 'secret',
        ]);

        $failResponse = $this->postJson('/api/auth/register', [
            'name' => 'Sally',
            'email' => 'sally@me.com',
            'password' => 'secret',
            'confirm_password' => 'secret',
        ]);


        $failResponse
            ->assertStatus(409)
            ->assertJson([
                'success' => false,
            ]);
    }


    /**
     * Login user.
     */
    public function test_logging_in_success(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Sally',
            'email' => 'sally@me.com',
            'password' => 'secret',
            'confirm_password' => 'secret',
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'sally@me.com',
            'password' => 'secret',
        ]);


        $loginResponse->assertStatus(200)->assertJson([
            'success' => true,
        ])->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
            ],
        ]);
    }


    /**
     * Login user with wrong creds.
     */
    public function test_logging_in_wrong_credentials_fail(): void
    {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'sally@me.com',
            'password' => 'secret',
        ]);

        $loginResponse->assertStatus(401)->assertJson([
            'success' => false,
        ]);
    }

    /**
     * Logout user.
     */
    public function test_logging_out_success(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Sally',
            'email' => 'sally@me.com',
            'password' => 'secret',
            'confirm_password' => 'secret',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'sally@me.com',
            'password' => 'secret',
        ]);

        // Authorization header seems to be set automatically
        $logoutResponse = $this->postJson('/api/auth/logout', []);

        $logoutResponse->assertStatus(200)->assertJson([
            'success' => true,
        ]);
    }


    /**
     * Logout user that doesn't exist.
     */
    public function test_logging_out_fail(): void
    {
        $logoutResponse = $this->postJson('/api/auth/logout', []);

        $logoutResponse->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }


    /**
     * Get user profile.
     */
    public function test_get_logged_in_user_profile_success(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Sally',
            'email' => 'sally@me.com',
            'password' => 'secret',
            'confirm_password' => 'secret',
        ]);

        $this->postJson('/api/auth/login', [
            'email' => 'sally@me.com',
            'password' => 'secret',
        ]);

        // Authorization header seems to be set automatically
        $profileResponse = $this->postJson('/api/auth/profile', []);

        $profileResponse->assertStatus(200)->assertJson([
            'success' => true,
        ])->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    /**
     * Get unauthenticated user profile.
     */
    public function test_get_unauthenticated_user_profile_fail(): void
    {
        $profileResponse = $this->postJson('/api/auth/profile', []);

        $profileResponse->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }


    /**
     * Rotate/Refresh user's access token.
     */
    public function test_rotate_logged_in_user_token_success(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Sally',
            'email' => 'sally@me.com',
            'password' => 'secret',
            'confirm_password' => 'secret',
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'sally@me.com',
            'password' => 'secret',
        ]);

        $oldToken = $loginResponse['data']['access_token'];

        // Authorization header seems to be set automatically
        $refreshTokenResponse = $this->postJson('/api/auth/refresh', []);

        $refreshTokenResponse->assertStatus(200)->assertJson([
            'success' => true,
        ])->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
            ],
        ]);

        $this->assertNotEquals($oldToken, $refreshTokenResponse['data']['access_token']);
    }

    /**
     * Rotate/Refresh unauthenticated user's access token.
     */
    public function test_rotate_unauthenticated_user_token_fail(): void
    {
        $refreshTokenResponse = $this->postJson('/api/auth/refresh', []);

        $refreshTokenResponse->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
