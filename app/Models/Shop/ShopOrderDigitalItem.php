<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ShopProduct $product
 */
class ShopOrderDigitalItem extends Model
{
    /** @return BelongsTo<ShopOrder, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class, 'order_id');
    }

    /** @return BelongsTo<ShopDigitalCard, $this> */
    public function card(): BelongsTo
    {
        return $this->belongsTo(ShopDigitalCard::class, 'digital_card_id');
    }

    /** @return BelongsTo<ShopOrderItem, $this> */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(ShopOrderItem::class, 'physical_order_item_id');
    }
}
