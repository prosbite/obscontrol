<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scripture extends Model
{
    protected $fillable = [
        'reference',
        'text',
        'translation',
    ];
}
