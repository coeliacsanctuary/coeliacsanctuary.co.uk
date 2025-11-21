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
use App\Scopes\LiveScope;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

/**
 * @implements HasOpenGraphImageContract<$this>
 *
 * @property Eatery $eatery
 * @property string $short_name
 * @property string $full_name
 * @property string | null $average_rating
 * @property array{value: string, label: string} | null $average_expense
 */
class NationwideBranch extends Model implements HasOpenGraphImageContract, IsSearchable
{
    use ClearsCache;
    use HasEateryDetails;

    /** @use HasOpenGraphImage<$this> */
    use HasOpenGraphImage;

    use HasSealiacOverview;
    use Searchable;

    protected $table = 'wheretoeat_nationwide_branches';

    /** @phpstan-ignore-next-line */
    protected $appends = ['formattedAddress', 'fullLocation'];

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'live' => 'bool',
    ];

    public static function booted(): void
    {
        static::addGlobalScope(new LiveScope('wheretoeat_nationwide_branches.live'));

        static::saving(function (self $eatery) {
            if ( ! $eatery->slug) {
                $eatery->slug = $eatery->generateSlug();
            }

            return $eatery;
        });

        static::saved(function (self $branch): void {
            if (config('coeliac.generate_og_images') === false) {
                return;
            }

            $eatery = $branch->eatery()->withoutGlobalScopes()->firstOrFail();
            $town = $branch->town()->withoutGlobalScopes()->firstOrFail();

            CreateEatingOutOpenGraphImageJob::dispatch($branch);
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
        ];

        /** @var AlgoliaBuilder $searcher */
        $searcher = static::search();

        return $searcher->with($params);
    }

    /** @return Builder<static> */
    public static function databaseSearchAroundLatLng(LatLng $latLng, int|float $radius = 2, array $additionalColumns = []): Builder
    {
        return static::query()
            ->selectRaw('(
                        6371000 * acos (
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
            ->addSelect(['id', 'wheretoeat_id', 'lat', 'lng', 'name', ...$additionalColumns])
            ->orderBy('distance');
    }

    /** @return BelongsTo<Eatery, $this> */
    public function eatery(): BelongsTo
    {
        return $this->belongsTo(Eatery::class, 'wheretoeat_id');
    }

    public function link(): string
    {
        return '/' . implode('/', [
            'wheretoeat',
            'nationwide',
            $this->eatery->slug,
            $this->slug,
        ]);
    }

    public function absoluteLink(): string
    {
        return config('app.url') . $this->link();
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

        if (app(Request::class)->route('eatery')) {
            $eatery = app(Request::class)->route('eatery');

            if ($eatery instanceof Eatery) {
                /** @var Builder<static> $query */
                $query = $eatery->nationwideBranches()->where('slug', $value);

                return $query;
            }

            if (is_string($eatery)) {
                /** @var Builder<static> $query */
                $query = Eatery::query()->where('slug', $eatery)->firstOrFail()->nationwideBranches()->where('slug', $value);

                return $query;
            }
        }

        return $query->where('slug', $value);
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing(['town', 'county', 'country']);

        $name = $this->name !== '' ? $this->name : $this->eatery->name;

        return $this->transform([
            'title' => $this->relationLoaded('town') && $this->town ? $name . ', ' . $this->town->town : $name,
            'location' => $this->relationLoaded('town') && $this->relationLoaded('county') && $this->town && $this->county ? $this->town->town . ', ' . $this->county->county : '',
            'town' => $this->relationLoaded('town') && $this->town ? $this->town->town : '',
            'county' => $this->relationLoaded('county') && $this->county ? $this->county->county : '',
            'info' => $this->eatery?->info, /** @phpstan-ignore-line */
            'address' => $this->address,
            '_geoloc' => [
                'lat' => $this->lat,
                'lng' => $this->lng,
            ],
        ]);
    }

    public function shouldBeSearchable(): bool
    {
        return $this->live;
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
    protected function makeAllSearchableUsing(Builder $query)
    {
        return $query->with(['town', 'county', 'country']);
    }

    /** @return HasMany<EateryReport, $this> */
    public function reports(): HasMany
    {
        return $this->hasMany(EateryReport::class, 'branch_id');
    }

    /** @return HasMany<EateryReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(EateryReview::class, 'nationwide_branch_id');
    }

    /** @return HasManyThrough<EateryReviewImage, EateryReview, $this> */
    public function reviewImages(): HasManyThrough
    {
        return $this->hasManyThrough(EateryReviewImage::class, EateryReview::class, 'id', 'wheretoeat_review_id', 'id', 'id');
    }

    /** @return Attribute<non-falsy-string, never> */
    public function fullName(): Attribute
    {
        return Attribute::get(function () {
            if ($this->name) {
                return "{$this->name} ({$this->eatery->name}) - {$this->full_location}";
            }

            return "{$this->eatery->name} - {$this->full_location}";
        });
    }

    /** @return Attribute<string, never> */
    public function shortName(): Attribute
    {
        return Attribute::get(function () {
            if ($this->name) {
                return "{$this->name}, {$this->short_location}";
            }

            return $this->short_location;
        });
    }

    /** @return Attribute<string | null, never> */
    public function averageRating(): Attribute
    {
        return Attribute::get(fn () => $this->eatery->average_rating);
    }

    protected function cacheKey(): string
    {
        return 'eating-out';
    }
}
