<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Concerns\ClearsCache;
use App\Concerns\DisplaysMedia;
use App\Concerns\Faqs\Faqable;
use App\Concerns\HasSealiacOverview;
use App\Concerns\LinkableModel;
use App\Concerns\Shop\HasPrices;
use App\Contracts\Faqs\HasFaqs;
use App\Contracts\Search\IsSearchable;
use App\Enums\Shop\OrderState;
use App\Models\Media;
use App\Schema\ShopReturnPolicySchema;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\SchemaOrg\Contracts\ReviewContract;
use Spatie\SchemaOrg\Product as ProductSchema;
use Spatie\SchemaOrg\Schema;

/**
 * @property int $currentPrice
 * @property null | int $oldPrice
 * @property float $averageRating
 * @property float $average_rating
 * @property array{current_price: string, old_price?: string} $price
 * @property Carbon $created_at
 *
 * @implements HasFaqs<$this>
 */
class ShopProduct extends Model implements HasFaqs, HasMedia, IsSearchable
{
    use ClearsCache;
    use DisplaysMedia;

    /** @use Faqable<$this> */
    use Faqable;

    /** @use HasPrices<$this> */
    use HasPrices;

    use HasSealiacOverview;

    /** @use InteractsWithMedia<Media> */
    use InteractsWithMedia;

    use LinkableModel;
    use Searchable;

    protected static function booted(): void
    {
        static::addGlobalScope(fn (Builder $builder) => $builder->whereHas('variants'));
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'google_merchant_enabled' => 'boolean',
        ];
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

    /** @return HasMany<ShopFeedback, $this> */
    public function feedback(): HasMany
    {
        return $this->hasMany(ShopFeedback::class, 'product_id');
    }

    /** @return HasMany<ShopOrderReviewItem, $this> */
    public function reviews(): HasMany
    {
        return $this
            ->hasMany(ShopOrderReviewItem::class, 'product_id')
            ->whereIn('shop_order_review_items.id', ShopOrderReviewItem::deduplicatedIds());
    }

    /** @return HasOne<ShopProductAddOn, $this> */
    public function addOns(): HasOne
    {
        return $this->hasOne(ShopProductAddOn::class, 'product_id');
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
        return Attribute::get(function () {
            $this->loadMissing('reviews');

            return round($this->reviews->average('rating') * 2) / 2;
        });
    }

    protected function linkRoot(): string
    {
        return 'shop/product';
    }

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'metaTags' => $this->meta_keywords,
            'totalSales' => ShopOrderItem::query()
                ->where('product_id', $this->id)
                ->whereRelation('order', 'state_id', OrderState::SHIPPED)
                ->sum('quantity'),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->variants->filter(fn (ShopProductVariant $variant) => $variant->live)->count() > 0;
    }

    public function isInStock(): bool
    {
        $this->loadMissing('variants');

        return $this->variants->contains(fn (ShopProductVariant $variant) => $variant->quantity > 0);
    }

    protected function baseShippingRate(): int
    {
        $this->loadMissing(['variants', 'shippingMethod.prices']);

        return $this
            ->shippingMethod
            ?->prices
            ->where('postage_country_area_id', 1)
            ->where('max_weight', '>', $this->variants->first()?->weight)
            ->sortBy('price')
            ->first()
            ->price ?? 0;
    }

    public function schema(): ProductSchema
    {
        $this->loadMissing(['reviews.parent', 'variants', 'prices', 'shippingMethod.prices']);

        return Schema::product()
            ->sku((string) $this->id)
            ->name($this->title)
            ->brand(
                Schema::brand()
                    ->name('Coeliac Sanctuary')
                    ->logo(asset('/images/logo.svg'))
            )
            ->description($this->description)
            ->image($this->main_image)
            ->offers(
                Schema::offer()
                    ->price($this->currentPrice / 100)
                    ->priceValidUntil($this->currentPrices()->first()->end_at ?? now()->addYear()->startOfDay())
                    ->availability($this->isInStock() ? Schema::itemAvailability()::InStock : Schema::itemAvailability()::OutOfStock)
                    ->priceCurrency('GBP')
                    ->url($this->absolute_link)
                    ->hasMerchantReturnPolicy(ShopReturnPolicySchema::make())
                    ->shippingDetails(
                        Schema::offerShippingDetails()
                            ->shippingDestination(Schema::definedRegion()->addressCountry('GB'))
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
                                    ->setProperty('cutoffTime', '14:00:00Z')
                                    ->handlingTime(Schema::quantitativeValue()->minValue(0)->maxValue(1)->unitCode('DAY'))
                                    ->transitTime(Schema::quantitativeValue()->minValue(1)->maxValue(3)->unitCode('DAY'))
                            )
                            ->shippingRate(
                                Schema::monetaryAmount()
                                    ->currency('GBP')
                                    ->value($this->baseShippingRate() / 100)
                            )
                    )
            )
            ->if(
                $this->reviews->isNotEmpty(),
                function (ProductSchema $schema) {
                    /** @var list<ReviewContract> $reviews */
                    $reviews = [];

                    foreach ($this->reviews->sortByDesc('created_at') as $review) {
                        if ($review->review === null || $review->review === '' || $review->created_at === null) {
                            continue;
                        }

                        $reviews[] = Schema::review()
                            ->reviewRating(Schema::rating()->ratingValue($review->rating)->bestRating(5))
                            ->reviewBody($review->review)
                            ->datePublished($review->created_at)
                            ->author(Schema::person()->name($review->parent?->name ?: ''));

                        if (count($reviews) === 20) {
                            break;
                        }
                    }

                    return $schema
                        ->review($reviews)
                        ->aggregateRating(
                            Schema::aggregateRating()
                                ->ratingValue(round((float) $this->reviews->average('rating'), 2))
                                ->reviewCount($this->reviews->count())
                        );
                }
            );
    }

    protected function cacheKey(): string
    {
        return 'products';
    }
}
