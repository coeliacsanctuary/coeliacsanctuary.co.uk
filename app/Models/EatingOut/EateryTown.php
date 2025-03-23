<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use App\Concerns\DisplaysMedia;
use App\Concerns\HasOpenGraphImage;
use App\Contracts\HasOpenGraphImageContract;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\Media;
use Error;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @implements HasOpenGraphImageContract<$this>
 */
class EateryTown extends Model implements HasMedia, HasOpenGraphImageContract
{
    use DisplaysMedia;

    /** @use HasOpenGraphImage<$this> */
    use HasOpenGraphImage;

    /** @use InteractsWithMedia<Media> */
    use InteractsWithMedia;

    protected $table = 'wheretoeat_towns';

    protected static function booted(): void
    {
        static::addGlobalScope(
            'hasPlaces',
            fn (Builder $builder) => $builder
                ->whereHas('liveEateries')
                ->orWhereHas('liveBranches')
        );

        static::creating(static function (self $town) {
            if ( ! $town->slug) {
                $town->slug = Str::slug($town->town);
                $town->legacy = $town->slug;
            }

            return $town;
        });

        static::saved(function (self $town): void {
            if (config('coeliac.generate_og_images') === false) {
                return;
            }

            CreateEatingOutOpenGraphImageJob::dispatch($town);
            CreateEatingOutOpenGraphImageJob::dispatch($town->county()->withoutGlobalScopes()->firstOrFail());
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @param  Builder<static>  $query
     * @param  string  $value
     * @param  ?string  $field
     * @return Builder<static>
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if (app(Request::class)->wantsJson()) {
            return $query->where('id', $value);
        }

        if (app(Request::class)->route('county')) {
            /** @var ?EateryCounty $county | string */
            $county = app(Request::class)->route('county');
            if ( ! $county instanceof EateryCounty) {
                $county = EateryCounty::query()->where('slug', $county)->firstOrFail();
            }

            /** @var Builder<static> $return */
            $return = $county->towns()->where('slug', $value)->getQuery();

            return $return;
        }

        return $query->where('slug', $value);
    }

    /** @return HasMany<Eatery, $this> */
    public function eateries(): HasMany
    {
        return $this->hasMany(Eatery::class, 'town_id');
    }

    /** @return HasMany<Eatery, $this> */
    public function liveEateries(): HasMany
    {
        /** @var HasMany<Eatery, $this> $relation */
        $relation = $this->hasMany(Eatery::class, 'town_id')->where('live', true);

        if ( ! request()->routeIs('eating-out.show')) {
            $relation->where('closed_down', false);
        }

        return $relation;
    }

    /** @return HasMany<NationwideBranch, $this> */
    public function liveBranches(): HasMany
    {
        return $this->hasMany(NationwideBranch::class, 'town_id')->where('live', true);
    }

    /** @return BelongsTo<EateryCounty, $this> */
    public function county(): BelongsTo
    {
        return $this->belongsTo(EateryCounty::class, 'county_id');
    }

    /** @return HasManyThrough<EateryReview, Eatery, $this> */
    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(EateryReview::class, Eatery::class, 'town_id', 'wheretoeat_id');
    }

    public function link(): string
    {
        if ($this->slug === 'nationwide') {
            return '/wheretoeat/nationwide';
        }

        return '/' . implode('/', [
            'wheretoeat',
            $this->county?->slug,
            $this->slug,
        ]);
    }

    public function keywords(): array
    {
        return [
            "gluten free {$this->town}", "coeliac {$this->town} eateries", "gluten free {$this->town} eateries",
            'gluten free places to eat in the uk', "gluten free places to eat in {$this->town}",
            'gluten free places to eat', 'gluten free cafes', 'gluten free restaurants', 'gluten free uk',
            'places to eat', 'cafes', 'restaurants', 'eating out', 'catering to coeliac', 'eating out uk',
            'gluten free venues', 'gluten free dining', 'gluten free directory', 'gf food',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('primary')->singleFile();
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
}
