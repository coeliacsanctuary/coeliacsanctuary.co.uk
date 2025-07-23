<?php

declare(strict_types=1);

namespace App\Models\EatingOut;

use Algolia\ScoutExtended\Builder as AlgoliaBuilder;
use App\Concerns\ClearsCache;
use App\Concerns\EatingOut\HasEateryDetails;
use App\Concerns\HasOpenGraphImage;
use App\Concerns\HasSealiacOverview;
use App\Contracts\HasOpenGraphImageContract;
use App\Contracts\Search\IsSearchable;
use App\DataObjects\EatingOut\LatLng;
use App\Jobs\OpenGraphImages\CreateEateryAppPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryMapPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Schema\EaterySchema;
use App\Scopes\LiveScope;
use App\Support\Helpers;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;
use Spatie\SchemaOrg\Restaurant;

/**
 * @implements HasOpenGraphImageContract<$this>
 *
 * @property string | null $average_rating
 * @property array{value: string, label: string} | null $average_expense
 * @property bool | null $has_been_rated
 * @property int | null $rating
 * @property int | null $rating_count
 * @property string $full_name
 * @property string $typeDescription
 */
class Eatery extends Model implements HasOpenGraphImageContract, IsSearchable
{
    use ClearsCache;
    use HasEateryDetails;
    /** @use HasOpenGraphImage<$this> */
    use HasOpenGraphImage;

    use HasSealiacOverview;

    use Searchable;

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'live' => 'bool',
        'closed_down' => 'bool',
    ];

    protected $table = 'wheretoeat';

    public static function booted(): void
    {
        static::addGlobalScope(new LiveScope());

        static::saving(function (self $eatery) {
            if ( ! $eatery->slug) {
                $eatery->slug = $eatery->generateSlug();
            }

            if ($eatery->type_id === 3) {
                $eatery->venue_type_id = 26;
            }

            if ( ! $eatery->cuisine_id) {
                $eatery->cuisine_id = 1;
            }

            return $eatery;
        });

        static::saved(function (self $eatery): void {
            if (config('coeliac.generate_og_images') === false) {
                return;
            }

            $town = $eatery->town()->withoutGlobalScopes()->firstOrFail();

            CreateEatingOutOpenGraphImageJob::dispatch($eatery);
            CreateEatingOutOpenGraphImageJob::dispatch($town);
            CreateEatingOutOpenGraphImageJob::dispatch($town->county()->withoutGlobalScopes()->firstOrFail());
            CreateEateryAppPageOpenGraphImageJob::dispatch();
            CreateEateryMapPageOpenGraphImageJob::dispatch();
            CreateEateryIndexPageOpenGraphImageJob::dispatch();
        });
    }

    public static function algoliaSearchAroundLatLng(LatLng $latLng, int|float $radius = 2): AlgoliaBuilder
    {
        $params = [
            'aroundLatLng' => $latLng->toString(),
            'aroundRadius' => Helpers::milesToMeters($radius),
            'getRankingInfo' => true,
            'hitsPerPage' => 1000,
        ];

        /** @var AlgoliaBuilder $searcher */
        $searcher = static::search();

        return $searcher->with($params);
    }

    /** @return Builder<static> */
    public static function databaseSearchAroundLatLng(LatLng $latLng, int|float $radius = 2): Builder
    {
        return static::query()
            ->selectRaw('(
                        3959 * acos (
                          cos ( radians(?) )
                          * cos( radians( lat ) )
                          * cos( radians( lng ) - radians(?) )
                          + sin ( radians(?) )
                          * sin( radians( lat ) )
                        )
                     ) AS distance', [
                $latLng->lat,
                $latLng->lng,
                $latLng->lat,
            ])
            ->having('distance', '<=', $radius)
            ->addSelect(['id', 'lat', 'lng', 'name', 'county_id', 'type_id'])
            ->where('closed_down', false)
            ->orderBy('distance');
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if (app(Request::class)->wantsJson()) {
            return $query->where('id', $value);
        }

        if (app(Request::class)->route('area')) {
            /** @var EateryArea | string $area */
            $area = app(Request::class)->route('area');
            if ( ! $area instanceof EateryArea) {
                $area = EateryArea::query()->where('slug', $area)->firstOrFail();
            }

            /** @var Builder<static> $return */
            $return = $area->liveEateries()->where('slug', $value)->getQuery();

            return $return;
        }

        if (app(Request::class)->route('town')) {
            /** @var EateryTown | string $town */
            $town = app(Request::class)->route('town');
            if ( ! $town instanceof EateryTown) {
                $town = EateryTown::query()->where('slug', $town)->firstOrFail();
            }

            /** @var Builder<static> $return */
            $return = $town->liveEateries()->where('slug', $value)->getQuery();

            return $return;
        }

        return $query->where('slug', $value);
    }

    public function link(): string
    {
        if ($this->county?->slug === 'nationwide') {
            return "/wheretoeat/nationwide/{$this->slug}";
        }

        if ($this->area) {
            return '/' . implode('/', [
                'wheretoeat',
                'london',
                $this->town?->slug,
                $this->area->slug,
                $this->slug,
            ]);
        }

        return '/' . implode('/', [
            'wheretoeat',
            $this->county?->slug,
            $this->town?->slug,
            $this->slug,
        ]);
    }

    public function absoluteLink(): string
    {
        return config('app.url') . $this->link();
    }

    /** @return HasOne<EateryCuisine, $this> */
    public function cuisine(): HasOne
    {
        return $this->hasOne(EateryCuisine::class, 'id', 'cuisine_id');
    }

    /** @return BelongsToMany<EateryFeature, $this> */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(
            EateryFeature::class,
            'wheretoeat_assigned_features',
            'wheretoeat_id',
            'feature_id'
        )->withTimestamps();
    }

    /** @return HasMany<NationwideBranch, $this> */
    public function nationwideBranches(): HasMany
    {
        return $this->hasMany(NationwideBranch::class, 'wheretoeat_id');
    }

    /** @return HasOne<NationwideBranch, $this> */
    public function branch(): HasOne
    {
        return $this->hasOne(NationwideBranch::class, 'wheretoeat_id');
    }

    /** @return HasOne<EateryOpeningTimes, $this> */
    public function openingTimes(): HasOne
    {
        return $this->hasOne(EateryOpeningTimes::class, 'wheretoeat_id', 'id');
    }

    /** @return HasMany<EateryReport, $this> */
    public function reports(): HasMany
    {
        return $this->hasMany(EateryReport::class, 'wheretoeat_id');
    }

    /** @return HasMany<EateryAttractionRestaurant, $this> */
    public function restaurants(): HasMany
    {
        return $this->hasMany(EateryAttractionRestaurant::class, 'wheretoeat_id', 'id');
    }

    /** @return HasOne<EateryReview, $this> */
    public function adminReview(): HasOne
    {
        return $this->hasOne(EateryReview::class, 'wheretoeat_id', 'id')
            ->where('admin_review', true)
            ->latest();
    }

    /** @return HasMany<EateryReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(EateryReview::class, 'wheretoeat_id', 'id');
    }

    /** @return HasMany<EateryReviewImage, $this> */
    public function reviewImages(): HasMany
    {
        return $this->hasMany(EateryReviewImage::class, 'wheretoeat_id', 'id');
    }

    /** @return HasMany<EateryReviewImage, $this> */
    public function approvedReviewImages(): HasMany
    {
        return $this->hasMany(EateryReviewImage::class, 'wheretoeat_id', 'id')
            ->whereRelation('review', 'approved', true);
    }

    /** @return HasOne<EateryType, $this> */
    public function type(): HasOne
    {
        return $this->hasOne(EateryType::class, 'id', 'type_id');
    }

    /** @return HasOne<EateryVenueType, $this> */
    public function venueType(): HasOne
    {
        return $this->hasOne(EateryVenueType::class, 'id', 'venue_type_id');
    }

    /** @return HasMany<EaterySuggestedEdit, $this> */
    public function suggestedEdits(): HasMany
    {
        return $this->hasMany(EaterySuggestedEdit::class, 'wheretoeat_id', 'id');
    }

    /** @return Attribute<array{value: string, label: string} | null, never> */
    public function averageExpense(): Attribute
    {
        return Attribute::get(function () {
            if ( ! $this->relationLoaded('reviews')) {
                return null;
            }

            $reviewsWithHowExpense = array_filter($this->reviews->flatten()->pluck('how_expensive')->toArray());

            if (count($reviewsWithHowExpense) === 0) {
                return null;
            }

            $average = round(Arr::average($reviewsWithHowExpense));

            return [
                'value' => (string) $average,
                'label' => EateryReview::HOW_EXPENSIVE_LABELS[$average],
            ];
        });
    }

    /** @return Attribute<string | null, never> */
    public function averageRating(): Attribute
    {
        return Attribute::get(function () {
            if ( ! $this->relationLoaded('reviews')) {
                return null;
            }

            return (string) Arr::average($this->reviews->pluck('rating')->toArray());
        });
    }

    /** @return Attribute<string, never> */
    public function fullName(): Attribute
    {
        return Attribute::get(function () {
            if ( ! $this->relationLoaded('town') || ! $this->town) {
                return $this->name;
            }

            if (Str::lower($this->town->town) === 'nationwide') {
                return "{$this->name} Nationwide Chain";
            }

            return implode(', ', array_filter([
                $this->name,
                $this->area?->area,
                $this->town->town,
                $this->county?->county,
                $this->country?->country,
            ]));
        });
    }

    /** @return Attribute<bool, never> */
    public function hasBeenRated(): Attribute
    {
        return Attribute::get(function () {
            if ( ! $this->relationLoaded('reviews')) {
                return false;
            }

            return $this->reviews
                ->where('ip', Container::getInstance()->make(Request::class)->ip())
                ->count() > 0;
        });
    }

    /** @return Attribute<string | null, never> */
    public function typeDescription(): Attribute
    {
        return Attribute::get(function () {
            if ( ! $this->relationLoaded('type') || ! $this->type) {
                return null;
            }

            return $this->type->name;
        });
    }

    /**
     * @param  Builder<static>  $builder
     * @return Builder<static>
     */
    public function scopeHasCategories(Builder $builder, array $categories): Builder
    {
        return $builder->where(fn (Builder $builder) => $builder->whereHas('type', fn (Builder $builder) => $builder->whereIn('type', $categories)));
    }

    /**
     * @param  Builder<static>  $builder
     * @return Builder<static>
     */
    public function scopeHasVenueTypes(Builder $builder, array $venueTypes): Builder
    {
        return $builder->where(fn (Builder $builder) => $builder->whereHas('venueType', fn (Builder $builder) => $builder->whereIn('slug', $venueTypes)));
    }

    /**
     * @param  Builder<static>  $builder
     * @return Builder<static>
     */
    public function scopeHasFeatures(Builder $builder, array $features): Builder
    {
        return $builder->where(fn (Builder $builder) => $builder->whereHas('features', fn (Builder $builder) => $builder->whereIn('slug', $features)));
    }

    public function keywords(): array
    {
        $area = $this->area?->area;
        $town = $this->town?->town;

        $kw = [
            $this->name, $this->full_name, "{$this->name} gluten free",
            "gluten free {$town}", "coeliac {$town} eateries", "gluten free {$town} eateries",
            'gluten free places to eat in the uk', "gluten free places to eat in {$town}",
        ];

        if ($area) {
            $kw = array_merge($kw, [
                "gluten free {$area}", "coeliac {$area} eateries", "gluten free {$area} eateries",
                "gluten free places to eat in {$area}",
            ]);
        }

        return array_merge($kw, [
            'gluten free places to eat', 'gluten free cafes', 'gluten free restaurants', 'gluten free uk',
            'places to eat', 'cafes', 'restaurants', 'eating out', 'catering to coeliac', 'eating out uk',
            'gluten free venues', 'gluten free dining', 'gluten free directory', 'gf food',
        ]);
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing(['area', 'town', 'county', 'country']);

        return $this->transform([
            'title' => $this->relationLoaded('town') && $this->town ? $this->name . ', ' . $this->town->town : $this->name,
            'location' => $this->relationLoaded('town') && $this->town && $this->relationLoaded('county') && $this->county ? $this->town->town . ', ' . $this->county->county : '',
            'area' => $this->relationLoaded('area') && $this->area ? $this->area->area : '',
            'town' => $this->relationLoaded('town') && $this->town ? $this->town->town : '',
            'county' => $this->relationLoaded('county') && $this->county ? $this->county->county : '',
            'info' => $this->info,
            'address' => $this->address,
            '_geoloc' => [
                'lat' => $this->lat,
                'lng' => $this->lng,
            ],
        ]);
    }

    public function shouldBeSearchable(): bool
    {
        return $this->county?->county !== 'Nationwide' && $this->live && ! $this->closed_down;
    }

    /** @return Attribute<float | null, callable(float): void> */
    public function distance(): Attribute
    {
        return Attribute::make(
            get: fn () => Arr::get($this->attributes, 'distance'),
            set: fn ($distance) => $this->attributes['distance'] = $distance,
        );
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with(['area', 'town', 'county', 'country']);
    }

    protected function cacheKey(): string
    {
        return 'eating-out';
    }

    public function schema(): Restaurant
    {
        return EaterySchema::make($this);
    }
}
