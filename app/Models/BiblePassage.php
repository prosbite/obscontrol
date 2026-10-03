<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiblePassage extends Model
{
    protected $fillable = [
        'provider',
        'translation',
        'reference_key',
        'label',
        'text',
        'translation_abbr',
        'copyright',
        'verses',
        'fetched_at',
    ];

    protected $casts = [
        'verses' => 'array',
        'fetched_at' => 'datetime',
    ];
}
