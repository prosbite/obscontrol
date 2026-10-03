<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScriptureRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reference' => ['required', 'string', 'max:255'],
            'text' => ['required', 'string'],
            'translation' => ['nullable', 'string', 'max:50'],
            'provider' => ['nullable', 'string', 'max:50'],
            'translation_abbr' => ['nullable', 'string', 'max:50'],
            'canonical_reference' => ['nullable', 'string', 'max:500'],
            'fetched_at' => ['nullable', 'date'],
        ];
    }
}
