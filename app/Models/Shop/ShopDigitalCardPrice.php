<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table(name: 'shop_digital_card_prices')]
class ShopDigitalCardPrice extends Model
{
    /** @return BelongsTo<ShopDigitalCard, $this> */
    public function card(): BelongsTo
    {
        return $this->belongsTo(ShopDigitalCard::class, 'digital_card_id');
    }
}
