<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property Carbon $last_fetched_at
 */
class GoogleStaticMap extends Model
{
    protected function casts(): array
    {
        return [
            'last_fetched_at' => 'datetime',
        ];
    }
}
