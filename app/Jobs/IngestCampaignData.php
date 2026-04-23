<?php

namespace App\Jobs;

use App\Models\CampaignData;
use App\Models\CampaignDataIngestLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class IngestCampaignData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<int, array{user_id:string, video_url:string, custom_fields?:array<mixed>}>  $rows
     */
    public function __construct(
        public int $campaignId,
        public string $requestId,
        public array $rows,
    ) {}

    public function handle(): void
    {
        $log = CampaignDataIngestLog::where('request_id', $this->requestId)->first();
        if ($log) {
            $log->update(['status' => 'processing']);
        }

        $received = count($this->rows);
        $inserted = 0;
        $updated = 0;
        $failed = 0;

        foreach (array_chunk($this->rows, 1000) as $chunk) {
            // Determine which rows will be updates vs inserts for reporting.
            $userIds = array_values(array_unique(array_map(
                static fn (array $r) => (string) $r['user_id'],
                $chunk
            )));

            $existing = DB::table('campaign_data')
                ->where('campaign_id', $this->campaignId)
                ->whereIn('user_id', $userIds)
                ->pluck('user_id')
                ->all();
            $existingSet = array_fill_keys(array_map('strval', $existing), true);

            $now = now();
            $upsertRows = [];

            foreach ($chunk as $row) {
                try {
                    $userId = (string) $row['user_id'];
                    $upsertRows[] = [
                        'campaign_id' => $this->campaignId,
                        'user_id' => $userId,
                        'video_url' => (string) $row['video_url'],
                        'custom_fields' => array_key_exists('custom_fields', $row) ? json_encode($row['custom_fields']) : null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if (isset($existingSet[$userId])) {
                        $updated++;
                    } else {
                        $inserted++;
                    }
                } catch (\Throwable) {
                    $failed++;
                }
            }

            if ($upsertRows !== []) {
                CampaignData::upsert(
                    $upsertRows,
                    ['campaign_id', 'user_id'],
                    ['video_url', 'custom_fields', 'updated_at']
                );
            }
        }

        if ($log) {
            $log->update([
                'received_count' => $received,
                'inserted_count' => $inserted,
                'updated_count' => $updated,
                'duplicate_count' => $updated,
                'failed_count' => $failed,
                'status' => 'completed',
            ]);
        }
    }

    public function failed(\Throwable $e): void
    {
        CampaignDataIngestLog::where('request_id', $this->requestId)->update([
            'status' => 'failed',
            'error_message' => $e->getMessage(),
        ]);
    }
}

