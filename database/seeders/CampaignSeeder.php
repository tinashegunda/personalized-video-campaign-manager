<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\Client;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $campaignCount = (int) env('SEED_CAMPAIGNS', 5);

        $clients = Client::query()->get();
        if ($clients->isEmpty()) {
            return;
        }

        for ($i = 0; $i < max(0, $campaignCount); $i++) {
            Campaign::factory()
                ->for($clients->random())
                ->create();
        }
    }
}

