<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Scopes\LiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopProductVariant extends Model
{
    protected $casts = [
        'icon' => 'json',
        'live' => 'bool',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new LiveScope());
    }

    /** @return BelongsTo<ShopProduct, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class);
    }
}
