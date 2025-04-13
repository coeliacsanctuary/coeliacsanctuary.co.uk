<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ShopSource extends Model
{
    /** @return BelongsToMany<ShopOrder, $this> */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(ShopOrder::class, 'shop_order_sources', 'source_id', 'order_id')->withTimestamps();
    }
}
