<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScriptureRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reference' => ['sometimes', 'string', 'max:255'],
            'text' => ['sometimes', 'string'],
            'translation' => ['nullable', 'string', 'max:50'],
        ];
    }
}
