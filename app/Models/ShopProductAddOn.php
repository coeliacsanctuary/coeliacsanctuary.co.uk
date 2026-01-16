<?php

declare(strict_types=1);

namespace App\Models;

use App\Concerns\Shop\HasPrices;
use App\Models\Shop\ShopProduct;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ShopProductAddOn extends Model implements HasMedia
{
    /** @use HasPrices<$this> */
    use HasPrices;

    use InteractsWithMedia;

    /** @return BelongsTo<ShopProduct, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('download')
            ->useDisk('product-addons')
            ->singleFile();
    }
}
