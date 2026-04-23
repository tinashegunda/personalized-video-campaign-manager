<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCampaignRequest;
use App\Http\Resources\CampaignResource;
use App\Models\Campaign;
use Illuminate\Http\JsonResponse;

class CampaignController extends Controller
{
    public function store(StoreCampaignRequest $request): JsonResponse
    {
        $campaign = Campaign::create($request->validated());

        return (new CampaignResource($campaign))
            ->response()
            ->setStatusCode(201);
    }
}

