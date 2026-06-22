<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LowerThirdResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'subtitle' => $this->subtitle,
            'image' => $this->image,
            'template' => $this->template,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
