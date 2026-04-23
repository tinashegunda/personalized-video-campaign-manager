# Personalized Video Campaign Manager (Laravel API)

API-driven campaign management system for **personalized video campaigns**. Clients can create campaigns and ingest per-user video records asynchronously via Laravel queues.

## Tech
- PHP 8.x
- Laravel 12.x
- MySQL
- Laravel Queues (uses the `jobs` table by default)
- Docker via **Laravel Sail**

## Project location
This project is intended to live at:
- `/var/www/personalized-video-campaign-manager`

## Setup (Laravel Sail)
This repository is structured to run with Sail.

### Run this repo locally

```bash
cp .env.example .env
composer install
php artisan key:generate

./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan queue:work
```

### Create a fresh project via laravel.build (optional)

```bash
cd /var/www
curl -s "https://laravel.build/personalized-video-campaign-manager?with=mysql" | bash
cd personalized-video-campaign-manager
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan queue:work
```

### Database env (MySQL + Sail)
Typical `.env` values:
- `DB_CONNECTION=mysql`
- `DB_HOST=mysql`
- `DB_PORT=3306`
- `DB_DATABASE=laravel`
- `DB_USERNAME=sail`
- `DB_PASSWORD=password`

## API
Base URL: `/api`

### POST `/api/campaigns`
Create a campaign.

**Request JSON**

```json
{
  "client_id": 1,
  "name": "Spring Launch",
  "start_date": "2026-04-01",
  "end_date": "2026-04-30"
}
```

**Response (201)**

```json
{
  "id": 1,
  "client_id": 1,
  "name": "Spring Launch",
  "start_date": "2026-04-01",
  "end_date": "2026-04-30",
  "created_at": "2026-04-23T11:00:00.000000Z",
  "updated_at": "2026-04-23T11:00:00.000000Z"
}
```

### POST `/api/campaigns/{campaign_id}/data`
Add user video data to a campaign. Returns **202 Accepted** and processes asynchronously.

**Request JSON**

```json
{
  "data": [
    {
      "user_id": "u1",
      "video_url": "https://example.com/v1.mp4",
      "custom_fields": { "country": "ZA", "tier": "pro" }
    },
    {
      "user_id": "u2",
      "video_url": "https://example.com/v2.mp4"
    }
  ]
}
```

**Response (202)**

```json
{
  "request_id": "00000000-0000-0000-0000-000000000001",
  "received_count": 2
}
```

#### Duplicate handling
Duplicates are identified by `(campaign_id, user_id)` and handled as **update/upsert**:
- `video_url` and `custom_fields` are overwritten by the newest payload.
- Duplicate attempts are tracked in `campaign_data_ingest_logs` (`updated_count` / `duplicate_count`).

#### Flexible custom fields
`custom_fields` is stored as JSON in the `campaign_data.custom_fields` column (no schema changes required for new keys).

## Background job
- Controller enqueues `IngestCampaignData` and returns `202`.
- The job processes in chunks and performs a DB upsert for speed.

Run the worker (Sail):

```bash
./vendor/bin/sail artisan queue:work
```

## Analytics report command
Counts-only campaign report:

```bash
./vendor/bin/sail artisan campaign:analytics 1
./vendor/bin/sail artisan campaign:analytics 1 --from=2026-04-01 --to=2026-04-30
```

## Tests

```bash
./vendor/bin/sail artisan test
```
