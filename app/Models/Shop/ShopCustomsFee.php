<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopCustomsFee extends Model
{
    /** @return BelongsTo<ShopPostageCountry, $this> */
    public function country(): BelongsTo
    {
        return $this->belongsTo(ShopPostageCountry::class, 'postage_country_id');
    }
}
