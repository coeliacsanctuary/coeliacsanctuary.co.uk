<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopDigitalCardPrice extends Model
{
    protected $table = 'shop_digital_card_prices';

    public function card(): BelongsTo
    {
        return $this->belongsTo(ShopDigitalCards::class, 'digital_card_id');
    }
}
