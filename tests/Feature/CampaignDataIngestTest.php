<?php

use App\Jobs\IngestCampaignData;
use App\Models\Campaign;
use App\Models\CampaignData;
use App\Models\CampaignDataIngestLog;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('accepts campaign data and dispatches a job', function () {
    Queue::fake();

    $client = Client::create(['name' => 'Acme']);
    $campaign = Campaign::create([
        'client_id' => $client->id,
        'name' => 'Launch',
        'start_date' => '2026-04-01',
        'end_date' => null,
    ]);

    $res = $this->postJson("/api/campaigns/{$campaign->id}/data", [
        'data' => [
            [
                'user_id' => 'u1',
                'video_url' => 'https://example.com/v1.mp4',
                'custom_fields' => ['country' => 'ZA'],
            ],
            [
                'user_id' => 'u2',
                'video_url' => 'https://example.com/v2.mp4',
            ],
        ],
    ]);

    $res->assertStatus(202)
        ->assertJsonStructure(['request_id', 'received_count']);

    expect(CampaignDataIngestLog::query()->count())->toBe(1);

    Queue::assertPushed(IngestCampaignData::class, function (IngestCampaignData $job) use ($campaign) {
        return $job->campaignId === $campaign->id
            && count($job->rows) === 2;
    });
});

it('upserts duplicate user_id within a campaign', function () {
    $client = Client::create(['name' => 'Acme']);
    $campaign = Campaign::create([
        'client_id' => $client->id,
        'name' => 'Launch',
        'start_date' => '2026-04-01',
        'end_date' => null,
    ]);

    $requestId = '00000000-0000-0000-0000-000000000001';

    CampaignDataIngestLog::create([
        'campaign_id' => $campaign->id,
        'request_id' => $requestId,
        'received_count' => 2,
        'status' => 'queued',
    ]);

    CampaignData::create([
        'campaign_id' => $campaign->id,
        'user_id' => 'u1',
        'video_url' => 'https://example.com/old.mp4',
        'custom_fields' => ['country' => 'ZA'],
    ]);

    (new IngestCampaignData(
        campaignId: $campaign->id,
        requestId: $requestId,
        rows: [
            [
                'user_id' => 'u1',
                'video_url' => 'https://example.com/new.mp4',
                'custom_fields' => ['country' => 'ZW', 'tier' => 'pro'],
            ],
            [
                'user_id' => 'u2',
                'video_url' => 'https://example.com/v2.mp4',
            ],
        ],
    ))->handle();

    expect(CampaignData::query()->where('campaign_id', $campaign->id)->count())->toBe(2);

    $u1 = CampaignData::query()->where('campaign_id', $campaign->id)->where('user_id', 'u1')->firstOrFail();
    expect($u1->video_url)->toBe('https://example.com/new.mp4');
    expect($u1->custom_fields)->toMatchArray(['country' => 'ZW', 'tier' => 'pro']);

    $log = CampaignDataIngestLog::query()->where('request_id', $requestId)->firstOrFail();
    expect($log->status)->toBe('completed');
    expect($log->received_count)->toBe(2);
    expect($log->inserted_count)->toBe(1);
    expect($log->updated_count)->toBe(1);
    expect($log->duplicate_count)->toBe(1);
});

