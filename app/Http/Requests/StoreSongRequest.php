<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSongRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'slides' => ['nullable', 'array'],
            'slides.*.slide_order' => ['required', 'integer', 'min:0'],
            'slides.*.content' => ['required', 'string'],
        ];
    }
}
