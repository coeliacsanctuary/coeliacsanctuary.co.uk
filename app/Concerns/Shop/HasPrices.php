<?php

declare(strict_types=1);

namespace App\Concerns\Shop;

use App\Models\Shop\ShopPrice;
use App\Support\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Money\Money;

/**
 * @template T of Model
 *
 * @mixin Model
 */
trait HasPrices
{
    /** @return MorphMany<ShopPrice, T> */
    public function prices(): MorphMany
    {
        return $this->morphMany(ShopPrice::class, 'purchasable');
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
