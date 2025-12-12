<?php

namespace Tests;

use App\Models\Organisme;
use App\Models\Projet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Create and authenticate a user for testing
     */
    protected function actingAsUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $token = $user->createToken('test-token')->plainTextToken;
        $this->withHeader('Authorization', 'Bearer ' . $token);
        return $user;
    }

    /**
     * Create an organisme for testing
     */
    protected function createOrganisme(array $attributes = []): Organisme
    {
        if (!isset($attributes['createdBy'])) {
            $user = User::factory()->create();
            $attributes['createdBy'] = $user->id;
        }
        return Organisme::factory()->create($attributes);
    }

    /**
     * Create a projet for testing
     */
    protected function createProjet(array $attributes = []): Projet
    {
        return Projet::factory()->create($attributes);
    }
}
