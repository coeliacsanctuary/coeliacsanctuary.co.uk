<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property array<string, mixed> $payload
 */
class NovaPreview extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }
}
