<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Song;
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
            'lyrics' => $this->lyrics,
            'slides' => Song::parseLyricsToSlides($this->lyrics),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
