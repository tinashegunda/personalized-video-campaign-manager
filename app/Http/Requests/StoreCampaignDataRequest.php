<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data' => ['required', 'array', 'min:1'],
            'data.*.user_id' => ['required', 'string', 'max:255'],
            'data.*.video_url' => ['required', 'url', 'max:2048'],
            'data.*.custom_fields' => ['nullable', 'array'],
        ];
    }
}

