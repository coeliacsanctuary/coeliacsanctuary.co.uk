<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class ShopPaymentRefund extends Model
{
    /** @return BelongsTo<ShopPayment, $this> */
    public function shopPayment(): BelongsTo
    {
        return $this->belongsTo(ShopPayment::class, 'payment_id');
    }

    /** @return HasOneThrough<ShopOrder, ShopPayment, $this> */
    public function order(): HasOneThrough
    {
        return $this->hasOneThrough(ShopOrder::class, ShopPayment::class, 'id', 'id', 'payment_id', 'order_id');
    }

    protected function casts(): array
    {
        return [
            'response' => 'array',
        ];
    }
}
