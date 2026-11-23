<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use App\Actions\EatingOut\TripPlanner\DecodePolylineAction;
use App\DataObjects\EatingOut\TripRoute;
use App\Enums\EatingOut\TripSummaryStatus;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property string $key
 * @property string $edit_token
 * @property array<int, array{label: string, lat: float, lng: float}> $points
 * @property array<int, array{from: int, to: int, distance: float, duration: float}> $timings
 * @property int $distance
 * @property int | null $stop_interval
 * @property string $geometry
 * @property float $route_distance
 * @property float $route_duration
 * @property array{overview: string, stops: array<int, array{number: int, summary: string}>} | null $ai_summary
 * @property TripSummaryStatus | null $ai_summary_status
 * @property Carbon | null $last_visited_at
 */
#[RouteKey('key')]
#[Table(name: 'wheretoeat_trips')]
class EateryTrip extends Model
{
    use MassPrunable;

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

    /** @return Builder<static> */
    public function prunable(): Builder
    {
        $expiry = now()->minus(months: 6);

        return static::query()->where(fn (Builder $query) => $query
            ->where('last_visited_at', '<=', $expiry)
            ->orWhere(fn (Builder $query) => $query->whereNull('last_visited_at')->where('created_at', '<=', $expiry)));
    }

    public function canBeEditedWith(?string $token): bool
    {
        if ($token === null) {
            return false;
        }

        return hash_equals($this->edit_token, $token);
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
            'ai_summary' => 'array',
            'ai_summary_status' => TripSummaryStatus::class,
            'distance' => 'int',
            'stop_interval' => 'int',
            'route_distance' => 'float',
            'route_duration' => 'float',
            'last_visited_at' => 'datetime',
        ];
    }
}
