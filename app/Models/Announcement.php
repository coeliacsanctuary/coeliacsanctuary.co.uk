<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected function casts(): array
    {
        return [
            'live' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }
}
