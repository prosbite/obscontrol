<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SongResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'artist' => $this->artist,
            'category' => $this->category,
            'slides' => SongSlideResource::collection($this->whenLoaded('slides')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
