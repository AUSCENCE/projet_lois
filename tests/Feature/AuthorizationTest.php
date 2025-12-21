<?php

namespace Tests\Feature;

use App\Models\Projet;
use App\Models\User;
use App\Models\Organisme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['id' => 1]);
        Organisme::factory()->create(['id' => 1, 'createdBy' => 1, 'updatedBy' => 1]);
    }

    /** @test */
    public function only_depute_can_vote()
    {
        $projet = Projet::factory()->create(['avoter' => true, 'organisme_id' => 1]);

        // Test with non-depute
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $response = $this->postJson("/api/projet/voter/{$projet->id}", ['vote' => true]);
        $response->assertStatus(403);

        // Test with depute
        $depute = User::factory()->create(['role' => 'depute']);
        $this->actingAs($depute);

        $response = $this->postJson("/api/projet/voter/{$projet->id}", ['vote' => true]);
        $response->assertStatus(200);
    }

    /** @test */
    public function only_admin_can_change_etat()
    {
        $projet = Projet::factory()->create(['avoter' => false, 'organisme_id' => 1]);

        // Test with non-admin
        $user = User::factory()->create(['role' => 'depute']);
        $this->actingAs($user);

        $response = $this->getJson("/api/projet/changeEtat/{$projet->id}");
        $response->assertStatus(403);

        // Test with admin
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->getJson("/api/projet/changeEtat/{$projet->id}");
        $response->assertStatus(200);
    }

    /** @test */
    public function only_admin_can_delete_projet()
    {
        $projet = Projet::factory()->create(['organisme_id' => 1]);

        // Test with non-admin
        $user = User::factory()->create(['role' => 'depute']);
        $this->actingAs($user);

        $response = $this->deleteJson("/api/projet/delete/{$projet->id}");
        $response->assertStatus(403);

        // Test with admin
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->deleteJson("/api/projet/delete/{$projet->id}");
        $response->assertStatus(200);
    }
}
