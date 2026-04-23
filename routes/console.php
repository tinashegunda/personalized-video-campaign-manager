<?php

use App\Console\Commands\CampaignAnalyticsReport;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('campaign:analytics {campaign_id} {--from=} {--to=}', CampaignAnalyticsReport::class)
    ->purpose('Generate counts-only analytics for a campaign');
