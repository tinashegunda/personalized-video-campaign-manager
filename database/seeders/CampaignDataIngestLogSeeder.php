<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignDataIngestLog;
use Illuminate\Database\Seeder;

class CampaignDataIngestLogSeeder extends Seeder
{
    public function run(): void
    {
        $perCampaign = (int) env('SEED_INGEST_LOGS_PER_CAMPAIGN', 5);
        if ($perCampaign <= 0) {
            return;
        }

        $campaigns = Campaign::query()->get();
        if ($campaigns->isEmpty()) {
            return;
        }

        $fakerSeed = env('SEED_FAKER_SEED');
        if ($fakerSeed !== null && $fakerSeed !== '') {
            // Offset the seed so logs vary from campaign data even when fixed.
            fake()->seed(((int) $fakerSeed) + 1);
        }

        foreach ($campaigns as $campaign) {
            CampaignDataIngestLog::factory()
                ->count($perCampaign)
                ->for($campaign)
                ->create();
        }
    }
}

