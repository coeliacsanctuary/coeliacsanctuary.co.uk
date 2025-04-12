<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleStaticMap extends Model
{
    protected function casts(): array
    {
        return [
            'last_fetched_at' => 'datetime',
        ];
    }
}
