<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Actions\Shop\ReconcileBasketItemsAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ShopOrder $order
 * @property ShopProduct $product
 */
class ShopOrderItem extends Model
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

    /** @return BelongsTo<ShopProduct, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }

    /** @return BelongsTo<ShopProductVariant, $this> */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ShopProductVariant::class, 'product_variant_id');
    }

    /** @return BelongsTo<ShopProductAddOn, $this> */
    public function addOn(): BelongsTo
    {
        return $this->belongsTo(ShopProductAddOn::class, 'product_add_on_id');
    }
}
