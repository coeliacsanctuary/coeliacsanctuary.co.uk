<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Concerns\ClearsCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopOrderReviewItem extends Model
{
    use ClearsCache;

    protected $casts = [
        'rating' => 'float',
    ];

    /** @return BelongsTo<ShopProduct, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }

    /** @return BelongsTo<ShopOrderReview, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ShopOrderReview::class, 'review_id');
    }

    /** @return BelongsTo<ShopOrder, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class, 'order_id');
    }

    protected function cacheKey(): string
    {
        return 'shop-reviews';
    }
}
