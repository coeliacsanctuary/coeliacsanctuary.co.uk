<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopOrderDownloadViews extends Model
{
    /** @return BelongsTo<ShopOrderDownloadLink, $this> */
    public function downloadLink(): BelongsTo
    {
        return $this->belongsTo(ShopOrderDownloadLink::class, 'shop_order_download_link_id');
    }
}
