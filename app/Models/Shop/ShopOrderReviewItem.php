<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Concerns\ClearsCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class ShopOrderReviewItem extends Model
{
    use ClearsCache;

    protected $casts = [
        'rating' => 'float',
    ];

    public static function deduplicatedIds(): QueryBuilder
    {
        return DB::query()
            ->select('id')
            ->fromSub(
                DB::table('shop_order_review_items')
                    ->select('id')
                    ->selectRaw("ROW_NUMBER() OVER (PARTITION BY review_id, product_id ORDER BY (review IS NULL OR review = '') ASC, id ASC) AS rn"),
                'ranked'
            )
            ->where('rn', 1);
    }

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
