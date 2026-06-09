<?php

declare(strict_types=1);

namespace App\Models;

use App\DataObjects\EatingOut\LatLng;
use Illuminate\Database\Eloquent\Model;

class GoogleGeocodeCache extends Model
{
    protected $table = 'google_geocode_cache';

    protected $casts = [
        'response' => 'array',
        'most_recent_hit' => 'datetime',
    ];

    public function toLatLng(): LatLng
    {
        return new LatLng((float) $this->response['lat'], (float) $this->response['lng']);
    }
}
