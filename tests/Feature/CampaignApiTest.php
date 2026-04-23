<?php

use App\Models\Campaign;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a campaign', function () {
    $client = Client::create(['name' => 'Acme']);

    $res = $this->postJson('/api/campaigns', [
        'client_id' => $client->id,
        'name' => 'Spring Launch',
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-30',
    ]);

    $res->assertCreated()
        ->assertJsonPath('client_id', $client->id)
        ->assertJsonPath('name', 'Spring Launch')
        ->assertJsonPath('start_date', '2026-04-01')
        ->assertJsonPath('end_date', '2026-04-30');

    expect(Campaign::query()->count())->toBe(1);
});

it('validates campaign creation', function () {
    $res = $this->postJson('/api/campaigns', [
        'client_id' => 999999,
        'name' => '',
        'start_date' => 'not-a-date',
        'end_date' => '2026-01-01',
    ]);

    $res->assertStatus(422)
        ->assertJsonValidationErrors(['client_id', 'name', 'start_date']);
});

