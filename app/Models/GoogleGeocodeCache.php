<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use App\DataObjects\EatingOut\LatLng;
use Illuminate\Database\Eloquent\Model;

#[Table(name: 'google_geocode_cache')]
class GoogleGeocodeCache extends Model
{
    public function toLatLng(): LatLng
    {
        return new LatLng((float) $this->response['lat'], (float) $this->response['lng']);
    }
    protected function casts(): array
    {
        return [
            'response' => 'array',
            'most_recent_hit' => 'datetime',
        ];
    }
}
