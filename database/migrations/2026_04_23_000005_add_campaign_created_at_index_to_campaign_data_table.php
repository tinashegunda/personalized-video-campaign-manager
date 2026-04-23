<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_data', function (Blueprint $table) {
            $table->index(['campaign_id', 'created_at'], 'campaign_data_campaign_id_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_data', function (Blueprint $table) {
            $table->dropIndex('campaign_data_campaign_id_created_at_index');
        });
    }
};

