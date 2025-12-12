<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_register_a_new_user()
    {
        $response = $this->postJson('/api/user/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'datas' => [
                    'token',
                    'user' => ['id', 'name', 'email']
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
    }

    /** @test */
    public function it_fails_to_register_with_duplicate_email()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/user/register', [
            'name' => 'Another User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_fails_to_register_without_password_confirmation()
    {
        $response = $this->postJson('/api/user/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/user/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'datas' => [
                    'token',
                    'user' => ['id', 'name', 'email']
                ]
            ]);
    }

    /** @test */
    public function it_fails_to_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/user/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_can_logout_authenticated_user()
    {
        $user = $this->actingAsUser();

        $response = $this->postJson('/api/user/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out'
            ]);
    }

    /** @test */
    public function it_can_get_authenticated_user_profile()
    {
        $user = $this->actingAsUser([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->getJson('/api/user/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'datas' => ['id', 'name', 'email']
            ])
            ->assertJson([
                'datas' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ]
            ]);
    }

    /** @test */
    public function it_can_refresh_authentication_token()
    {
        $user = $this->actingAsUser();

        $response = $this->postJson('/api/user/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'datas' => [
                    'token',
                    'user' => ['id', 'name', 'email']
                ]
            ]);
    }

    /** @test */
    public function it_requires_authentication_for_protected_routes()
    {
        $response = $this->getJson('/api/user/me');
        $response->assertStatus(401);

        $response = $this->postJson('/api/user/logout');
        $response->assertStatus(401);

        $response = $this->postJson('/api/user/refresh');
        $response->assertStatus(401);
    }
}
