<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property bool $is_open_now
 * @property array $opening_times_array
 */
class EateryOpeningTimes extends Model
{
    protected $casts = ['is_open_now' => 'bool'];

    protected $table = 'wheretoeat_opening_times';

    /** @return BelongsTo<Eatery, $this> */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class, 'wheretoeat_id');
    }

    /** @return Attribute<bool, never> */
    public function isOpenNow(): Attribute
    {
        return Attribute::get(function () {
            $today = $this->currentDay();

            $todaysOpeningTime = $this->formatTime($today . '_start');
            $todaysClosingTime = $this->formatTime($today . '_end');

            if ( ! $todaysOpeningTime || ! $todaysClosingTime) {
                return false;
            }

            $opensAt = Carbon::now()->clone()->setTime(...$todaysOpeningTime);
            $closesAt = Carbon::now()->clone()->setTime(...$todaysClosingTime);

            if ($closesAt->hour < $opensAt->hour) {
                $closesAt->addDay();
            }

            return Carbon::now()->isBetween($opensAt, $closesAt);
        });
    }

    public function closesAt(?string $day = null): ?string
    {
        if ( ! $day) {
            $day = $this->currentDay();
        }

        return $this->timeToString($day, 'end');
    }

    public function opensAt(?string $day = null): ?string
    {
        if ( ! $day) {
            $day = $this->currentDay();
        }

        return $this->timeToString($day, 'start');
    }

    public function formatTime(string $column): ?array
    {
        $value = $this->$column;

        if ( ! $value) {
            return null;
        }

        $bits = explode(':', $value);

        return [
            (int) $bits[0],
            (int) ($bits[1] ?? 0),
            (int) ($bits[2] ?? 0),
        ];
    }

    protected function timeToString(string $today, string $suffix): ?string
    {
        /** @var array $time */
        $time = $this->formatTime("{$today}_{$suffix}");

        if ( ! $time) {
            return 'Closed';
        }

        if ($time[0] === 0 && $time[1] === 0) {
            return 'midnight';
        }

        if ($time[0] === 12 && $time[1] === 0) {
            return 'midday';
        }

        $timeSuffix = $time[0] < 12 ? 'am' : 'pm';

        if ($time[1] === 0) {
            return ($time[0] > 12 ? $time[0] - 12 : $time[0]) . $timeSuffix;
        }

        return "{$time[0]}:{$time[1]}{$timeSuffix}";
    }

    /** @return Attribute<array<mixed>, never> */
    public function openingTimesArray(): Attribute
    {
        return Attribute::get(fn () => collect(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])
            ->mapWithKeys(fn (string $day) => [
                $day => [
                    'opens' => $this->opensAt($day),
                    'closes' => $this->closesAt($day),
                ],
            ])
            ->toArray());
    }

    protected function currentDay(): string
    {
        return Str::lower(Carbon::now()->dayName);
    }
}
