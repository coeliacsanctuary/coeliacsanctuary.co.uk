<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Actions\Shop\ReconcileBasketItemsAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ShopOrder $order
 */
class ShopOrderDigitalItem extends Model
{
    protected static function booted(): void
    {
        static::saved(fn (self $item) => app(ReconcileBasketItemsAction::class)->handle($item->order));
        static::deleted(fn (self $item) => app(ReconcileBasketItemsAction::class)->handle($item->order));
    }

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
