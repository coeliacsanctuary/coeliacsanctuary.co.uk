<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property ShopProduct $product
 */
class ShopOrderItem extends Model
{
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
