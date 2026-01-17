<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Concerns\Shop\HasPrices;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $currentPrice
 * @property null | int $oldPrice
 * @property array{current_price: string, old_price?: string} $price
 * @property Carbon $created_at
 */
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
