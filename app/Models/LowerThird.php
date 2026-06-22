<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LowerThird extends Model
{
    protected $fillable = [
        'name',
        'subtitle',
        'image',
        'template',
    ];

    protected function casts(): array
    {
        return [
            'template' => 'string',
        ];
    }
}
