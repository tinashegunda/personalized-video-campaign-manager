<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampaignData>
 */
class CampaignDataFactory extends Factory
{
    /**
     * @var class-string<\App\Models\CampaignData>
     */
    protected $model = CampaignData::class;

    public function definition(): array
    {
        $tier = fake()->randomElement(['free', 'basic', 'pro', 'enterprise']);
        $country = fake()->countryCode();

        return [
            'campaign_id' => Campaign::factory(),
            'user_id' => 'u_' . fake()->unique()->bothify('########??'),
            'video_url' => fake()->url() . '/videos/' . fake()->uuid() . '.mp4',
            'custom_fields' => [
                'country' => $country,
                'tier' => $tier,
                'source' => fake()->randomElement(['ads', 'email', 'organic', 'partner']),
            ],
        ];
    }
}

