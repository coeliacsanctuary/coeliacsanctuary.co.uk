<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Concerns\ClearsCache;
use App\Concerns\DisplaysMedia;
use App\Concerns\HasSealiacOverview;
use App\Concerns\LinkableModel;
use App\Contracts\Search\IsSearchable;
use App\Enums\Shop\OrderState;
use App\Models\Media;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\SchemaOrg\Contracts\ReviewContract;
use Spatie\SchemaOrg\Product as ProductSchema;
use Spatie\SchemaOrg\Schema;

/**
 * @property float $averageRating
 * @property float $average_rating
 * @property int $from_price
 * @property Carbon $created_at
 */
class ShopProduct extends Model implements HasMedia, IsSearchable
{
    use ClearsCache;
    use DisplaysMedia;
    use HasSealiacOverview;

    /** @use InteractsWithMedia<Media> */
    use InteractsWithMedia;

    use LinkableModel;
    use Searchable;

    protected static function booted(): void
    {
        static::addGlobalScope(fn (Builder $builder) => $builder->whereHas('variants'));
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if (app(Request::class)->wantsJson()) {
            return $query->where('id', $value); /** @phpstan-ignore-line */
        }

        /** @phpstan-ignore-next-line  */
        return $query->where('slug', $value)->orWhere('legacy_slug', $value);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('primary')->singleFile();

        $this->addMediaCollection('social')->singleFile();

        $this->addMediaCollection('additional');
    }

    public function registerMediaConversions(?\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        if ( ! $media || $media->extension === 'webp') {
            return;
        }

        $this
            ->addMediaConversion('webp')
            ->performOnCollections('primary', 'additional')
            ->nonQueued()
            ->format('webp');
    }

    /** @return BelongsToMany<ShopCategory, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ShopCategory::class, 'shop_product_categories', 'product_id', 'category_id');
    }

    /** @return BelongsTo<ShopShippingMethod, $this> */
    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShopShippingMethod::class);
    }

    /** @return HasMany<ShopProductVariant, $this> */
    public function variants(): HasMany
    {
        return $this->hasMany(ShopProductVariant::class, 'product_id');
    }

    public function primaryVariant(): ShopProductVariant
    {
        $this->loadMissing('variants');

        /** @var ShopProductVariant $variant */
        $variant = $this->variants->firstWhere('primary_variant', true) ?? $this->variants->first();

        return $variant;
    }

    /** @return HasMany<ShopPrice, $this> */
    public function prices(): HasMany
    {
        return $this->hasMany(ShopPrice::class, 'product_id');
    }

    /** @return HasMany<ShopFeedback, $this> */
    public function feedback(): HasMany
    {
        return $this->hasMany(ShopFeedback::class, 'product_id');
    }

    /** @return HasMany<ShopOrderReviewItem, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(ShopOrderReviewItem::class, 'product_id');
    }

    /** @return BelongsToMany<TravelCardSearchTerm, $this> */
    public function travelCardSearchTerms(): BelongsToMany
    {
        return $this->belongsToMany(
            TravelCardSearchTerm::class,
            'shop_product_assigned_travel_card_search_terms',
            'product_id',
            'search_term_id',
        )->withTimestamps()->withPivot(['card_language', 'card_score', 'card_show_on_product_page']);
    }

    public function getScoutKey(): mixed
    {
        return $this->id;
    }

    /** @return Attribute<float, never> */
    public function averageRating(): Attribute
    {
        return Attribute::get(fn () => round($this->reviews->average('rating') * 2) / 2);
    }

    protected function linkRoot(): string
    {
        return 'shop/product';
    }

    public function toSearchableArray(): array
    {
        return $this->transform([
            'title' => $this->title,
            'description' => $this->description,
            'metaTags' => $this->meta_keywords,
            'totalSales' => ShopOrderItem::query()
                ->where('product_id', $this->id)
                ->whereRelation('order', 'state_id', OrderState::SHIPPED)
                ->sum('quantity'),
        ]);
    }

    public function shouldBeSearchable(): bool
    {
        return $this->variants->filter(fn (ShopProductVariant $variant) => $variant->live)->count() > 0;
    }

    protected function isInStock(): bool
    {
        return $this
            ->variants()
            ->pluck('quantity')
            ->filter(fn ($quantity) => $quantity > 0)
            ->count() > 0;
    }

    protected function baseShippingRate(): int
    {
        return $this
            ->shippingMethod()
            ->first()
            ?->prices()
            ->where('postage_country_area_id', 1)
            ->where('max_weight', '>', $this->variants[0]?->weight)
            ->orderBy('price')
            ->firstOrFail()
            ->price ?? 0;
    }

    /** @return Attribute<int, never> */
    public function fromPrice(): Attribute
    {
        return Attribute::get(function () {
            $this->loadMissing('variants.prices');

            return $this->variants->pluck('prices')->flatten()->sortBy('price')->first()->price;
        });
    }

    public function hasMultiplePrices(): bool
    {
        $this->loadMissing('variants.prices');

        return $this->variants->pluck('price.current_price')->unique()->count() > 1;
    }

    public function schema(): ProductSchema
    {
        return Schema::product()
            ->sku((string) $this->id)
            ->name($this->title)
            ->brand(
                Schema::organization()
                    ->name('Coeliac Sanctuary')
                    ->logo(asset('/images/logo.svg'))
            )
            ->description($this->description)
            ->image($this->main_image)
            ->offers(
                Schema::offer()
                    ->price($this->primaryVariant()->currentPrice / 100)
                    ->availability($this->isInStock() ? Schema::itemAvailability()::InStock : Schema::itemAvailability()::OutOfStock)
                    ->priceCurrency('GBP')
                    ->url($this->absolute_link)
                    ->shippingDetails(
                        Schema::offerShippingDetails()
                            ->shippingDestination(Schema::definedRegion()->addressCountry('UK'))
                            ->deliveryTime(
                                Schema::shippingDeliveryTime()
                                    ->businessDays(
                                        Schema::openingHoursSpecification()
                                            ->dayOfWeek([
                                                Schema::dayOfWeek()::Monday,
                                                Schema::dayOfWeek()::Tuesday,
                                                Schema::dayOfWeek()::Wednesday,
                                                Schema::dayOfWeek()::Thursday,
                                                Schema::dayOfWeek()::Friday,
                                            ])
                                    )
                                    ->cutoffTime(Carbon::createFromTime(14))
                                    ->handlingTime(Schema::quantitativeValue()->minValue(0)->maxValue(1))
                                    ->transitTime(Schema::quantitativeValue()->minValue(1)->maxValue(3))
                            )
                            ->shippingRate(
                                Schema::monetaryAmount()
                                    ->currency('GBP')
                                    ->value($this->baseShippingRate() / 100)
                            )
                    )
            )
            ->if(
                $this->reviews()->count() > 0,
                function (ProductSchema $schema) {
                    /** @var array{ReviewContract} $reviews */
                    $reviews = $this->reviews()
                        ->latest()
                        ->with(['parent'])
                        ->get()
                        ->map(
                            fn (ShopOrderReviewItem $review) => Schema::review()
                                ->reviewRating(Schema::rating()->ratingValue($review->rating)->bestRating(5))
                                ->author(Schema::person()->name($review->parent?->name ?: ''))
                        )
                        ->toArray();

                    return $schema
                        ->reviews($reviews)
                        ->aggregateRating(
                            Schema::aggregateRating()
                                ->ratingValue((float) $this->reviews()->average('rating'))
                                ->reviewCount($this->reviews()->count())
                        );
                }
            );
    }

    protected function cacheKey(): string
    {
        return 'products';
    }
}
