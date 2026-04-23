<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Campaign>
 */
class CampaignFactory extends Factory
{
    /**
     * @var class-string<\App\Models\Campaign>
     */
    protected $model = Campaign::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-90 days', '+14 days');
        $end = fake()->boolean(80)
            ? (clone $start)->modify('+' . fake()->numberBetween(7, 60) . ' days')
            : null;

        return [
            'client_id' => Client::factory(),
            'name' => fake()->words(fake()->numberBetween(2, 4), true),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end?->format('Y-m-d'),
        ];
    }
}

