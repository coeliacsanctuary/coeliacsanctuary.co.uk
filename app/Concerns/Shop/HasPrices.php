<?php

declare(strict_types=1);

namespace App\Concerns\Shop;

use App\Models\Shop\ShopPrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
}
