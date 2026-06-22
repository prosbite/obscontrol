<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Song extends Model
{
    protected $fillable = [
        'title',
        'artist',
        'category',
    ];

    public function slides(): HasMany
    {
        return $this->hasMany(SongSlide::class)->orderBy('slide_order');
    }
}
