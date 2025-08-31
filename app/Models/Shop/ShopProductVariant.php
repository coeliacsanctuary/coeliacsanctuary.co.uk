<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Enums\Shop\ProductVariantType;
use App\Scopes\LiveScope;
use App\Support\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Money\Money;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $currentPrice
 * @property null | int $oldPrice
 * @property array{current_price: string, old_price?: string} $price
 */
class ShopProductVariant extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $casts = [
        'icon' => 'json',
        'live' => 'bool',
        'primary_variant' => 'bool',
        'variant_type' => ProductVariantType::class,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new LiveScope());
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('download')
            ->useDisk('digital-products')
            ->singleFile();
    }

    /** @return BelongsTo<ShopProduct, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class);
    }

    /** @return HasMany<ShopPrice, $this> */
    public function prices(): HasMany
    {
        return $this->hasMany(ShopPrice::class, 'variant_id');
    }

    /** @return Collection<int, ShopPrice> */
    public function currentPrices(): Collection
    {
        return $this->prices
            ->filter(fn (ShopPrice $price) => $price->start_at->lessThan(Carbon::now()))
            ->filter(fn (ShopPrice $price) => ! $price->end_at || $price->end_at->endOfDay()->greaterThan(Carbon::now()))
            ->sortByDesc('start_at');
    }

    /** @return Attribute<null | int, never> */
    public function currentPrice(): Attribute
    {
        return Attribute::get(fn () => $this->currentPrices()->first()?->price);
    }

    /** @return Attribute<null | int, never> */
    public function oldPrice(): Attribute
    {
        return Attribute::get(function () {
            if ((bool) $this->currentPrices()->first()?->sale_price === true) {
                return $this->currentPrices()->skip(1)->first()?->price;
            }

            return null;
        });
    }

    /** @return Attribute<array{current_price: string, old_price?: string}, never> */
    public function price(): Attribute
    {
        return Attribute::get(function () {
            $rtr = ['current_price' => Helpers::formatMoney(Money::GBP($this->currentPrice))];

            if ($this->oldPrice !== null && $this->oldPrice !== 0) {
                $rtr['old_price'] = Helpers::formatMoney(Money::GBP($this->oldPrice));
            }

            return $rtr;
        });
    }
}
