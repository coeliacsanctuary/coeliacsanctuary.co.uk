<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table(name: 'shop_discount_code_types')]
class ShopDiscountCodeType extends Model
{
    /** @return HasMany<ShopDiscountCode, $this> */
    public function codes(): HasMany
    {
        return $this->hasMany(ShopDiscountCode::class, 'type_id');
    }
}
