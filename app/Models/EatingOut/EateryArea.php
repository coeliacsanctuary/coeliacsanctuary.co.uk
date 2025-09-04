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
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @implements HasOpenGraphImageContract<$this>
 *
 * @property string $image
 */
class EateryArea extends Model implements HasMedia, HasOpenGraphImageContract
{
    use DisplaysMedia;

    /** @use HasOpenGraphImage<$this> */
    use HasOpenGraphImage;

    /** @use InteractsWithMedia<Media> */
    use InteractsWithMedia;

    protected $table = 'wheretoeat_areas';

    protected static function booted(): void
    {
        static::addGlobalScope(
            'hasPlaces',
            fn (Builder $builder) => $builder
                ->whereHas('liveEateries')
                ->orWhereHas('liveBranches')
        );

        static::creating(static function (self $area) {
            if ( ! $area->slug) {
                $area->slug = Str::slug($area->area);
            }

            if ( ! $area->latlng) {
                /** @phpstan-ignore-next-line  */
                $name = "{$area->area}, {$area->town->town}, {$area->town->county?->county}, {$area->town->county?->country?->country}";
                $latLng = app(LocationSearchService::class)->getLatLng($name, force: true);

                $area->latlng = $latLng->toString();
            }

            return $area;
        });

        static::saved(function (self $area): void {
            if (config('coeliac.generate_og_images') === false) {
                return;
            }

            CreateEatingOutOpenGraphImageJob::dispatch($area);
            CreateEatingOutOpenGraphImageJob::dispatch($area->town()->withoutGlobalScopes()->firstOrFail());
            CreateEatingOutOpenGraphImageJob::dispatch($area->town()->withoutGlobalScopes()->firstOrFail()->county()->withoutGlobalScopes()->firstOrFail());
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

        if (app(Request::class)->route('borough') || app(Request::class)->route('town')) {
            /** @var ?EateryTown $borough | string */
            $borough = app(Request::class)->route('borough') ?? app(Request::class)->route('town');

            if ( ! $borough instanceof EateryTown) {
                $borough = EateryTown::query()->where('slug', $borough)->firstOrFail();
            }

            /** @var Builder<static> $return */
            $return = $borough->areas()->where('slug', $value)->getQuery();

            return $return;
        }

        return $query->where('slug', $value);
    }

    /** @return HasMany<Eatery, $this> */
    public function eateries(): HasMany
    {
        return $this->hasMany(Eatery::class, 'area_id');
    }

    /** @return HasMany<Eatery, $this> */
    public function liveEateries(): HasMany
    {
        /** @var HasMany<Eatery, $this> $relation */
        $relation = $this->hasMany(Eatery::class, 'area_id')->where('live', true);

        if ( ! request()->routeIs('eating-out.show')) {
            $relation->where('closed_down', false);
        }

        return $relation;
    }

    /** @return HasMany<NationwideBranch, $this> */
    public function liveBranches(): HasMany
    {
        return $this->hasMany(NationwideBranch::class, 'area_id')->where('live', true);
    }

    /** @return BelongsTo<EateryTown, $this> */
    public function town(): BelongsTo
    {
        return $this->belongsTo(EateryTown::class, 'town_id');
    }

    /** @return HasManyThrough<EateryReview, Eatery, $this> */
    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(EateryReview::class, Eatery::class, 'area_id', 'wheretoeat_id');
    }

    public function link(): string
    {
        return '/' . implode('/', [
            'wheretoeat',
            'london',
            $this->town?->slug,
            $this->slug,
        ]);
    }

    public function absoluteLink(): string
    {
        return config('app.url') . $this->link();
    }

    public function keywords(): array
    {
        return [
            "gluten free {$this->area}", "coeliac {$this->area} eateries", "gluten free {$this->area} eateries",
            'gluten free places to eat in the uk', "gluten free places to eat in {$this->area}",
            'gluten free places to eat', 'gluten free cafes', 'gluten free restaurants', 'gluten free uk',
            'places to eat', 'cafes', 'restaurants', 'eating out', 'catering to coeliac', 'eating out uk',
            'gluten free venues', 'gluten free dining', 'gluten free directory', 'gf food',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('primary')->singleFile();
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        if ( ! $media || $media->extension === 'webp') {
            return;
        }

        $this
            ->addMediaConversion('webp')
            ->performOnCollections('primary')
            ->nonQueued()
            ->format('webp');
    }

    /** @return Attribute<non-falsy-string | null, never> */
    public function image(): Attribute
    {
        return Attribute::get(function () { /** @phpstan-ignore-line */
            try {
                return $this->main_image_as_webp;
            } catch (Error $exception) { /** @phpstan-ignore-line */
                return null;
            }
        });
    }
}
