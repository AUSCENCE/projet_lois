<?php

namespace Database\Seeders;

use App\Models\Organisme;
use Database\Factories\OrganismeFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrganismeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Organisme::factory(3);
    }
}
