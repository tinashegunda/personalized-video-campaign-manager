<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignDataRequest;
use App\Jobs\IngestCampaignData;
use App\Models\Campaign;
use App\Models\CampaignDataIngestLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CampaignDataController extends Controller
{
    public function store(StoreCampaignDataRequest $request, Campaign $campaign): JsonResponse
    {
        $payload = $request->validated()['data'];

        $requestId = (string) Str::uuid();

        CampaignDataIngestLog::create([
            'campaign_id' => $campaign->id,
            'request_id' => $requestId,
            'received_count' => count($payload),
            'status' => 'queued',
        ]);

        IngestCampaignData::dispatch(
            campaignId: $campaign->id,
            requestId: $requestId,
            rows: $payload,
        );

        return response()->json([
            'request_id' => $requestId,
            'received_count' => count($payload),
        ], 202);
    }
}

