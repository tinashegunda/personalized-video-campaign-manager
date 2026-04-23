<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\CampaignData;
use App\Models\CampaignDataIngestLog;
use Illuminate\Console\Command;

class CampaignAnalyticsReport extends Command
{
    protected $signature = 'campaign:analytics
        {campaign_id : Campaign ID}
        {--from= : Filter by created_at >= from (YYYY-MM-DD)}
        {--to= : Filter by created_at <= to (YYYY-MM-DD)}';

    protected $description = 'Generate counts-only analytics for a campaign';

    public function handle(): int
    {
        $campaignId = (int) $this->argument('campaign_id');

        /** @var Campaign|null $campaign */
        $campaign = Campaign::find($campaignId);
        if (! $campaign) {
            $this->error("Campaign {$campaignId} not found.");
            return self::FAILURE;
        }

        $from = $this->option('from');
        $to = $this->option('to');

        $dataQuery = CampaignData::query()->where('campaign_id', $campaignId);
        $logQuery = CampaignDataIngestLog::query()->where('campaign_id', $campaignId);

        if ($from) {
            $dataQuery->whereDate('created_at', '>=', $from);
            $logQuery->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $dataQuery->whereDate('created_at', '<=', $to);
            $logQuery->whereDate('created_at', '<=', $to);
        }

        $totalRows = (clone $dataQuery)->count();
        $distinctUsers = (clone $dataQuery)->distinct('user_id')->count('user_id');

        $ingestRequests = (clone $logQuery)->count();
        $received = (clone $logQuery)->sum('received_count');
        $inserted = (clone $logQuery)->sum('inserted_count');
        $updated = (clone $logQuery)->sum('updated_count');
        $duplicates = (clone $logQuery)->sum('duplicate_count');
        $failed = (clone $logQuery)->sum('failed_count');

        $this->line("Campaign: #{$campaign->id} {$campaign->name}");
        $this->line("Client:   #{$campaign->client_id}");
        $this->line('');

        $this->line('Campaign data (campaign_data):');
        $this->line("  Total rows:      {$totalRows}");
        $this->line("  Distinct users:  {$distinctUsers}");
        $this->line('');

        $this->line('Ingest logs (campaign_data_ingest_logs):');
        $this->line("  Requests:        {$ingestRequests}");
        $this->line("  Received items:  {$received}");
        $this->line("  Inserted items:  {$inserted}");
        $this->line("  Updated items:   {$updated}");
        $this->line("  Duplicates:      {$duplicates}");
        $this->line("  Failed items:    {$failed}");

        return self::SUCCESS;
    }
}

