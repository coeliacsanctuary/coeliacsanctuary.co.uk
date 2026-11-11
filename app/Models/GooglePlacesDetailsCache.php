<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property array<array-key, mixed> $response
 * @property int $hits
 * @property Carbon $last_fetched_at
 */
#[Table(name: 'google_places_details_cache')]
class GooglePlacesDetailsCache extends Model
{
    public function isFresh(): bool
    {
        return $this->last_fetched_at->addDays(30)->isFuture();
    }

    protected function casts(): array
    {
        return [
            'response' => 'array',
            'last_fetched_at' => 'datetime',
        ];
    }
}
