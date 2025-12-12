<?php

namespace Tests\Feature;

use App\Models\Organisme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganismeControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_all_organismes()
    {
        $user = $this->actingAsUser();
        
        Organisme::factory()->count(3)->create();

        $response = $this->getJson('/api/organisme');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'datas' => [
                    'data' => [
                        '*' => ['id', 'name', 'created_at', 'updated_at']
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_requires_authentication_to_list_organismes()
    {
        $response = $this->getJson('/api/organisme');
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_create_an_organisme()
    {
        $user = $this->actingAsUser();

        $response = $this->postJson('/api/organisme/store', [
            'name' => 'Assemblée Nationale',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'datas' => ['id', 'name']
            ]);

        $this->assertDatabaseHas('organismes', [
            'name' => 'Assemblée Nationale',
        ]);
    }

    /** @test */
    public function it_fails_to_create_organisme_with_duplicate_name()
    {
        $user = $this->actingAsUser();
        
        Organisme::factory()->create(['name' => 'Sénat']);

        $response = $this->postJson('/api/organisme/store', [
            'name' => 'Sénat',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_fails_to_create_organisme_without_name()
    {
        $user = $this->actingAsUser();

        $response = $this->postJson('/api/organisme/store', []);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_can_show_a_specific_organisme()
    {
        $user = $this->actingAsUser();
        
        $organisme = Organisme::factory()->create([
            'name' => 'Présidence',
        ]);

        $response = $this->getJson("/api/organisme/show/{$organisme->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Organisme trouvé',
                'datas' => [
                    'id' => $organisme->id,
                    'name' => 'Présidence',
                ]
            ]);
    }

    /** @test */
    public function it_can_update_an_organisme()
    {
        $user = $this->actingAsUser();
        
        $organisme = Organisme::factory()->create([
            'name' => 'Old Name',
        ]);

        $response = $this->putJson("/api/organisme/update/{$organisme->id}", [
            'name' => 'New Name',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('organismes', [
            'id' => $organisme->id,
            'name' => 'New Name',
        ]);
    }

    /** @test */
    public function it_can_delete_an_organisme()
    {
        $user = $this->actingAsUser();
        
        $organisme = Organisme::factory()->create();

        $response = $this->deleteJson("/api/organisme/delete/{$organisme->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('organismes', [
            'id' => $organisme->id,
        ]);
    }

    /** @test */
    public function it_requires_authentication_for_all_organisme_operations()
    {
        $user = User::factory()->create();
        $organisme = Organisme::factory()->create(['createdBy' => $user->id]);

        // Test without authentication
        $this->getJson('/api/organisme')->assertStatus(401);
        $this->postJson('/api/organisme/store', ['name' => 'Test'])->assertStatus(401);
        $this->getJson("/api/organisme/show/{$organisme->id}")->assertStatus(401);
        $this->putJson("/api/organisme/update/{$organisme->id}", ['name' => 'Test'])->assertStatus(401);
        $this->deleteJson("/api/organisme/delete/{$organisme->id}")->assertStatus(401);
    }
}
