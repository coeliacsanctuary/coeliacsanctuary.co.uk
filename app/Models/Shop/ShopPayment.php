<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShopPayment extends Model
{
    /** @return BelongsTo<ShopOrder, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class, 'order_id');
    }

    /** @return BelongsTo<ShopPaymentType, $this> */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ShopPaymentType::class, 'payment_type_id');
    }

    /** @return HasOne<ShopPaymentResponse, $this> */
    public function response(): HasOne
    {
        return $this->hasOne(ShopPaymentResponse::class, 'payment_id');
    }
}
