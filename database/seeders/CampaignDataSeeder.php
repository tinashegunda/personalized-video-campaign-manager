<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\CampaignData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CampaignDataSeeder extends Seeder
{
    public function run(): void
    {
        $totalRows = (int) env('SEED_CAMPAIGN_DATA', 1000);

        $campaigns = Campaign::query()->get();
        if ($campaigns->isEmpty() || $totalRows <= 0) {
            return;
        }

        $fakerSeed = env('SEED_FAKER_SEED');
        if ($fakerSeed !== null && $fakerSeed !== '') {
            fake()->seed((int) $fakerSeed);
        }

        $campaignCount = $campaigns->count();
        $basePerCampaign = intdiv($totalRows, $campaignCount);
        $remainder = $totalRows % $campaignCount;

        $now = now();
        $payload = [];

        foreach ($campaigns->values() as $idx => $campaign) {
            $perCampaign = $basePerCampaign + ($idx < $remainder ? 1 : 0);
            if ($perCampaign <= 0) {
                continue;
            }

            // Ensure uniqueness within (campaign_id, user_id).
            $userIds = [];
            while (count($userIds) < $perCampaign) {
                $candidate = 'u_' . Str::lower(Str::random(10));
                $userIds[$candidate] = true;
            }
            $userIds = array_keys($userIds);

            for ($i = 0; $i < $perCampaign; $i++) {
                $userId = $userIds[$i];

                $payload[] = [
                    'campaign_id' => $campaign->id,
                    'user_id' => $userId,
                    'video_url' => fake()->url() . '/videos/' . fake()->uuid() . '.mp4',
                    'custom_fields' => json_encode([
                        'country' => fake()->countryCode(),
                        'tier' => Arr::random(['free', 'basic', 'pro', 'enterprise']),
                        'source' => Arr::random(['ads', 'email', 'organic', 'partner']),
                    ]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        // Bulk insert for speed. (custom_fields is JSON, so we store a JSON string)
        CampaignData::query()->insert($payload);
    }
}

