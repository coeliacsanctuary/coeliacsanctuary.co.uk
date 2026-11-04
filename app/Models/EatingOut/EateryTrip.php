<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use App\Actions\EatingOut\TripPlanner\DecodePolylineAction;
use App\DataObjects\EatingOut\TripRoute;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $key
 * @property string $edit_token
 * @property array<int, array{label: string, lat: float, lng: float}> $points
 * @property array<int, array{from: int, to: int, distance: float, duration: float}> $timings
 * @property int $distance
 * @property string $geometry
 * @property float $route_distance
 * @property float $route_duration
 * @property Carbon | null $last_visited_at
 */
#[RouteKey('key')]
#[Table(name: 'wheretoeat_trips')]
class EateryTrip extends Model
{
    protected static function booted(): void
    {
        static::creating(static function (self $trip): void {
            do {
                $key = Str::random(8);
            } while (static::query()->where('key', $key)->exists());

            $trip->key = $key;
            $trip->edit_token = Str::random(40);
        });
    }

    public function toTripRoute(): TripRoute
    {
        return new TripRoute(
            geometry: $this->geometry,
            points: app(DecodePolylineAction::class)->handle($this->geometry),
            distance: (float) $this->route_distance,
            duration: (float) $this->route_duration,
            timings: $this->timings,
        );
    }

    public function recordVisit(): void
    {
        $this->timestamps = false;

        $this->update(['last_visited_at' => now()]);

        $this->timestamps = true;
    }

    protected function casts(): array
    {
        return [
            'points' => 'array',
            'timings' => 'array',
            'distance' => 'int',
            'route_distance' => 'float',
            'route_duration' => 'float',
            'last_visited_at' => 'datetime',
        ];
    }
}
