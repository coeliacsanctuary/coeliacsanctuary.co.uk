<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'open_route_service_autocomplete_cache')]
class OpenRouteServiceAutocompleteCache extends Model
{
    protected function casts(): array
    {
        return [
            'response' => 'array',
            'most_recent_hit' => 'datetime',
        ];
    }
}
