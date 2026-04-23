<?php

namespace Database\Factories;

use App\Models\Campaign;
use App\Models\CampaignDataIngestLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CampaignDataIngestLog>
 */
class CampaignDataIngestLogFactory extends Factory
{
    /**
     * @var class-string<\App\Models\CampaignDataIngestLog>
     */
    protected $model = CampaignDataIngestLog::class;

    public function definition(): array
    {
        $status = fake()->randomElement(['queued', 'processing', 'completed', 'failed']);
        $received = fake()->numberBetween(50, 400);

        $inserted = 0;
        $updated = 0;
        $failed = 0;
        $duplicates = 0;
        $error = null;

        if ($status === 'completed') {
            $failed = fake()->numberBetween(0, (int) floor($received * 0.02));
            $duplicates = fake()->numberBetween(0, (int) floor($received * 0.2));
            $inserted = fake()->numberBetween((int) floor($received * 0.4), $received - $failed);
            $updated = max(0, $received - $inserted - $failed);
        } elseif ($status === 'failed') {
            $failed = fake()->numberBetween(1, (int) floor($received * 0.3));
            $inserted = fake()->numberBetween(0, $received - $failed);
            $updated = max(0, $received - $inserted - $failed);
            $error = fake()->sentence();
        } elseif ($status === 'processing') {
            $inserted = fake()->numberBetween(0, (int) floor($received * 0.6));
            $updated = fake()->numberBetween(0, (int) floor($received * 0.3));
            $failed = fake()->numberBetween(0, (int) floor($received * 0.05));
        }

        $duplicates = min($duplicates, $received);
        $inserted = min($inserted, $received);
        $updated = min($updated, max(0, $received - $inserted));
        $failed = min($failed, max(0, $received - $inserted - $updated));

        return [
            'campaign_id' => Campaign::factory(),
            'request_id' => fake()->uuid(),
            'received_count' => $received,
            'inserted_count' => $inserted,
            'updated_count' => $updated,
            'duplicate_count' => $duplicates,
            'failed_count' => $failed,
            'status' => $status,
            'error_message' => $error,
        ];
    }
}

