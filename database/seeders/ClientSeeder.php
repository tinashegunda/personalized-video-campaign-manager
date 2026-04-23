<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $count = (int) env('SEED_CLIENTS', 3);

        Client::factory()->count(max(0, $count))->create();
    }
}

