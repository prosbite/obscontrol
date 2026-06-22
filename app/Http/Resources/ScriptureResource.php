<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScriptureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'text' => $this->text,
            'translation' => $this->translation,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
