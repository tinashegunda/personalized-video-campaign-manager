<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignDataIngestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'request_id',
        'received_count',
        'inserted_count',
        'updated_count',
        'duplicate_count',
        'failed_count',
        'status',
        'error_message',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}

