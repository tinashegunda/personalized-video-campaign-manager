<?php

use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\CampaignDataController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::post('/campaigns', [CampaignController::class, 'store']);
Route::post('/campaigns/{campaign}/data', [CampaignDataController::class, 'store']);
