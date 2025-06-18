<?php

declare(strict_types=1);

namespace App\Concerns\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

/**
 * @mixin Model
 *
 * @property string $full_location
 * @property string $short_location
 * @property null|string $slug
 * @property null|string|float $distance
 */
trait HasEateryDetails
{
    protected function hasDuplicateNameInTown(): bool
    {
        return self::query()
            ->where('town_id', $this->town_id)
            ->where('name', $this->name)
            ->where('live', 1)
            ->count() > 1;
    }

    protected function eateryPostcode(): string
    {
        $address = explode('<br />', $this->address);

        return array_pop($address);
    }

    public function generateSlug(): string
    {
        if ($this->slug) {
            return $this->slug;
        }

        /** @var EateryTown $town */
        $town = $this->town;

        return Str::of($this->name ?: $town->town)
            ->when(
                $this->hasDuplicateNameInTown(),
                fn (Stringable $str) => $str->append(' ' . $this->eateryPostcode()),
            )
            ->slug()
            ->toString();
    }

    /** @return BelongsTo<Eatery, $this> */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class, 'wheretoeat_id', 'id');
    }

    /** @return BelongsTo<EateryTown, $this> */
    public function town(): BelongsTo
    {
        return $this->belongsTo(EateryTown::class, 'town_id');
    }

    /** @return BelongsTo<EateryArea, $this> */
    public function area(): BelongsTo
    {
        return $this->belongsTo(EateryArea::class, 'area_id');
    }

    /** @return BelongsTo<EateryCounty, $this> */
    public function county(): BelongsTo
    {
        return $this->belongsTo(EateryCounty::class, 'county_id');
    }

    /** @return BelongsTo<EateryCountry, $this> */
    public function country(): BelongsTo
    {
        return $this->belongsTo(EateryCountry::class, 'country_id');
    }

    /** @return Attribute<string, never> */
    public function formattedAddress(): Attribute
    {
        return Attribute::get(fn () => Str::of($this->address)->explode('<br />')->join(', '));
    }

    /** @return Attribute<non-falsy-string | null, never> */
    public function fullLocation(): Attribute
    {
        return Attribute::get(function () {
            if ( ! $this->relationLoaded('town') || ! $this->relationLoaded('county') || ! $this->relationLoaded('country') || ! $this->town || ! $this->county || ! $this->country) {
                return null;
            }

            if (Str::lower($this->town->town) === 'nationwide') {
                return "{$this->name}, Nationwide";
            }

            return implode(', ', [
                $this->town->town,
                $this->county->county,
                $this->country->country,
            ]);
        });
    }

    /** @return Attribute<non-falsy-string | null, never> */
    public function shortLocation(): Attribute
    {
        return Attribute::get(function () {
            if ( ! $this->relationLoaded('town') || ! $this->relationLoaded('county') || ! $this->town || ! $this->county) {
                return null;
            }

            if (Str::lower($this->town->town) === 'nationwide') {
                return 'Nationwide';
            }

            return implode(', ', [
                $this->town->town,
                $this->county->county,
            ]);
        });
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeNationwide(Builder $query): Builder
    {
        return $query->where('county_id', 1);
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeNotNationwide(Builder $query): Builder
    {
        return $query->where('county_id', '!=', 1);
    }
}
