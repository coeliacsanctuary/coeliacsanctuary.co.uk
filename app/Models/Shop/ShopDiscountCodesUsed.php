<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'shop_discount_codes_used')]
class ShopDiscountCodesUsed extends Model
{
    /** @return BelongsTo<ShopDiscountCode, $this> */
    public function code(): BelongsTo
    {
        return $this->belongsTo(ShopDiscountCode::class, 'discount_id');
    }

    /** @return BelongsTo<ShopOrder, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class, 'order_id');
    }
}
