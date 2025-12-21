<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_new_user()
    {
        $this->actingAsUser();

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => 'user'
        ];

        $response = $this->postJson('/api/user', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'datas' => [
                    'user' => [
                        'name' => 'New User',
                        'email' => 'newuser@example.com',
                        'role' => 'user'
                    ],
                    'password' => 'password123'
                ]
            ]);

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    /** @test */
    public function it_can_list_all_users()
    {
        $this->actingAsUser();
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }

    /** @test */
    public function it_can_show_a_specific_user()
    {
        $this->actingAsUser();
        $targetUser = User::factory()->create();

        $response = $this->getJson("/api/user/{$targetUser->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'datas' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->name,
                ]
            ]);
    }

    /** @test */
    public function it_can_update_a_user_role()
    {
        $this->actingAsUser();
        $targetUser = User::factory()->create(['role' => 'user']);

        $response = $this->putJson("/api/user/{$targetUser->id}/role", [
            'role' => 'admin'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('admin', $targetUser->fresh()->role);
    }

    /** @test */
    public function it_can_delete_a_user()
    {
        $this->actingAsUser();
        $targetUser = User::factory()->create();

        $response = $this->deleteJson("/api/user/{$targetUser->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', ['id' => $targetUser->id]);
    }
}
