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
        ];
    }
}
