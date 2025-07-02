<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopPaymentRefund extends Model
{
    /** @return BelongsTo<ShopPayment, $this> */
    public function shopPayment(): BelongsTo
    {
        return $this->belongsTo(ShopPayment::class, 'payment_id');
    }

    /** @return BelongsTo<ShopOrder, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(ShopOrder::class, 'order_id');
    }

    protected function casts(): array
    {
        return [
            'response' => 'array',
        ];
    }
}
