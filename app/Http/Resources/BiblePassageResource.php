<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Services\Bible\BiblePassage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BiblePassageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var BiblePassage $passage */
        $passage = $this->resource;

        return [
            'reference' => $passage->reference,
            'text' => $passage->text,
            'translation' => $passage->translation,
            'translation_abbr' => $passage->translationAbbr,
            'copyright' => $passage->copyright,
            'verses' => $passage->verses,
            'provider' => $passage->provider,
        ];
    }
}
