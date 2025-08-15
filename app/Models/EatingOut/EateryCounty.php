<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use App\Concerns\DisplaysMedia;
use App\Concerns\HasOpenGraphImage;
use App\Contracts\HasOpenGraphImageContract;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\Media;
use App\Services\EatingOut\LocationSearchService;
use Error;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @implements HasOpenGraphImageContract<$this>
 *
 * @property string $image
 */
class EateryCounty extends Model implements HasMedia, HasOpenGraphImageContract
{
    use DisplaysMedia;

    /** @use HasOpenGraphImage<$this> */
    use HasOpenGraphImage;

    /** @use InteractsWithMedia<Media> */
    use InteractsWithMedia;

    protected $table = 'wheretoeat_counties';

    protected static function booted(): void
    {
        static::addGlobalScope('hasPlaces', fn (Builder $builder) => $builder->whereHas('activeTowns'));

        static::creating(static function (self $county) {
            if ( ! $county->slug) {
                $county->slug = Str::slug($county->county);
                $county->legacy = $county->slug;
            }

            if ( ! $county->latlng) {
                $latLng = app(LocationSearchService::class)->getLatLng("{$county->county}, {$county->country?->country}", force: true);

                $county->latlng = $latLng->toString();
            }

            return $county;
        });

        static::saved(fn (self $county) => CreateEatingOutOpenGraphImageJob::dispatch($county));
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('primary')->singleFile();
    }

    /** @return HasMany<EateryTown, $this> */
    public function activeTowns(): HasMany
    {
        return $this->hasMany(EateryTown::class, 'county_id')
            ->where(fn (Builder $builder) => $builder
                ->whereHas('liveEateries')
                ->orWhereHas('liveBranches'))
            ->orderBy('town');
    }

    /** @return HasMany<Eatery, $this> */
    public function eateries(): HasMany
    {
        return $this->hasMany(Eatery::class, 'county_id');
    }

    /** @return HasMany<NationwideBranch, $this> */
    public function nationwideBranches(): HasMany
    {
        return $this->hasMany(NationwideBranch::class, 'county_id');
    }

    /** @return HasManyThrough<EateryReview, Eatery, $this> */
    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(EateryReview::class, Eatery::class, 'county_id', 'wheretoeat_id');
    }

    /** @return HasMany<EateryTown, $this> */
    public function towns(): HasMany
    {
        return $this->hasMany(EateryTown::class, 'county_id');
    }

    /** @return BelongsTo<EateryCountry, $this> */
    public function country(): BelongsTo
    {
        return $this->belongsTo(EateryCountry::class, 'country_id');
    }

    public function keywords(): array
    {
        return [
            "coeliac {$this->county}", "gluten free {$this->county}", "gluten free food {$this->county}",
            "gluten free places to eat in {$this->county}", 'gluten free places to eat', 'gluten free cafes',
            'gluten free restaurants', 'gluten free uk', 'places to eat', 'cafes', 'restaurants', 'eating out',
            'catering to coeliac', 'eating out uk', 'gluten free venues', 'gluten free dining',
            'gluten free directory', 'gf food', $this->county,
        ];
    }

    /** @return Attribute<non-falsy-string | null, never> */
    public function image(): Attribute
    {
        return Attribute::get(function () { /** @phpstan-ignore-line */
            try {
                return $this->main_image;
            } catch (Error $exception) { /** @phpstan-ignore-line */
                return null;
            }
        });
    }

    public function link(): string
    {
        return '/' . implode('/', [
            'wheretoeat',
            $this->slug,
        ]);
    }

    public function absoluteLink(): string
    {
        return config('app.url') . $this->link();
    }
}
