<?php

namespace Tests\Feature;

use App\Models\Organisme;
use App\Models\Projet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjetControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_list_all_projets()
    {
        $user = $this->actingAsUser();
        $organisme = $this->createOrganisme();
        
        Projet::factory()->count(3)->create(['organisme_id' => $organisme->id]);

        $response = $this->getJson('/api/projet');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'datas' => [
                    'data' => [
                        '*' => ['id', 'title', 'filePath', 'organisme_id']
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_filter_promulgued_projets()
    {
        $user = $this->actingAsUser();
        $organisme = $this->createOrganisme();
        
        Projet::factory()->create(['organisme_id' => $organisme->id, 'etat' => 'promulgue']);
        Projet::factory()->create(['organisme_id' => $organisme->id, 'etat' => 'en_cours']);

        $response = $this->getJson('/api/projet/promuleguer');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_filter_non_promulgued_projets()
    {
        $user = $this->actingAsUser();
        $organisme = $this->createOrganisme();
        
        Projet::factory()->create(['organisme_id' => $organisme->id, 'etat' => 'promulgue']);
        Projet::factory()->create(['organisme_id' => $organisme->id, 'etat' => 'en_cours']);

        $response = $this->getJson('/api/projet/nonPromulegue');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_filter_projets_to_vote()
    {
        $user = $this->actingAsUser();
        $organisme = $this->createOrganisme();
        
        Projet::factory()->create(['organisme_id' => $organisme->id, 'avoter' => true]);
        Projet::factory()->create(['organisme_id' => $organisme->id, 'avoter' => false]);

        $response = $this->getJson('/api/projet/avoter');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_create_a_projet_with_pdf_file()
    {
        $user = $this->actingAsUser();
        $organisme = $this->createOrganisme();

        $file = UploadedFile::fake()->create('projet.pdf', 1000, 'application/pdf');

        $response = $this->postJson('/api/projet/store', [
            'title' => 'Loi de Finances 2025',
            'organisme_id' => $organisme->id,
            'filePath' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'datas' => ['id', 'title', 'organisme_id']
            ]);

        $this->assertDatabaseHas('projets', [
            'title' => 'Loi de Finances 2025',
            'organisme_id' => $organisme->id,
        ]);
    }

    /** @test */
    public function it_can_create_a_projet_without_file()
    {
        $user = $this->actingAsUser();
        $organisme = $this->createOrganisme();

        $response = $this->postJson('/api/projet/store', [
            'title' => 'Loi sans fichier',
            'organisme_id' => $organisme->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);
    }

    /** @test */
    public function it_fails_to_create_projet_with_duplicate_title()
    {
        $user = $this->actingAsUser();
        $organisme = $this->createOrganisme();
        
        Projet::factory()->create([
            'title' => 'Duplicate Title',
            'organisme_id' => $organisme->id,
        ]);

        $response = $this->postJson('/api/projet/store', [
            'title' => 'Duplicate Title',
            'organisme_id' => $organisme->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_fails_to_create_projet_without_required_fields()
    {
        $user = $this->actingAsUser();

        $response = $this->postJson('/api/projet/store', []);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_can_show_a_specific_projet()
    {
        $user = $this->actingAsUser();
        $organisme = $this->createOrganisme();
        
        $projet = Projet::factory()->create([
            'title' => 'Test Projet',
            'organisme_id' => $organisme->id,
        ]);

        $response = $this->getJson("/api/projet/show/{$projet->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Projet trouvé',
                'datas' => [
                    'id' => $projet->id,
                    'title' => 'Test Projet',
                ]
            ]);
    }

    /** @test */
    public function it_can_update_a_projet()
    {
        $user = $this->actingAsUser();
        $organisme = $this->createOrganisme();
        
        $projet = Projet::factory()->create([
            'title' => 'Old Title',
            'organisme_id' => $organisme->id,
        ]);

        $response = $this->postJson("/api/projet/update/{$projet->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_delete_a_projet()
    {
        $user = $this->actingAsUser();
        $organisme = $this->createOrganisme();
        
        $projet = Projet::factory()->create(['organisme_id' => $organisme->id]);

        $response = $this->deleteJson("/api/projet/delete/{$projet->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('projets', [
            'id' => $projet->id,
        ]);
    }

    /** @test */
    public function it_can_vote_on_a_projet()
    {
        $user = $this->actingAsUser(['role' => 'depute']);
        $organisme = $this->createOrganisme();
        
        $projet = Projet::factory()->create([
            'organisme_id' => $organisme->id,
            'avoter' => true,
            'cloturevoter' => now()->addDays(7)->format('Y-m-d')
        ]);

        $response = $this->postJson("/api/projet/voter/{$projet->id}", [
            'vote' => true,
            'commentaire' => 'Je suis en accord avec ce projet',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_requires_authentication_for_all_projet_operations()
    {
        $organisme = $this->createOrganisme();
        $projet = Projet::factory()->create(['organisme_id' => $organisme->id]);

        // Test without authentication
        $this->getJson('/api/projet')->assertStatus(401);
        $this->postJson('/api/projet/store', ['title' => 'Test'])->assertStatus(401);
        $this->getJson("/api/projet/show/{$projet->id}")->assertStatus(401);
        $this->postJson("/api/projet/update/{$projet->id}", ['title' => 'Test'])->assertStatus(401);
        $this->deleteJson("/api/projet/delete/{$projet->id}")->assertStatus(401);
        $this->postJson("/api/projet/voter/{$projet->id}", ['vote' => true])->assertStatus(401);
    }
}
