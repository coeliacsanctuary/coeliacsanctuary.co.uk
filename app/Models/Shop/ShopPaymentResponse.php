<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** @property Carbon $created_at */
class ShopPaymentResponse extends Model
{
    /** @return BelongsTo<ShopPayment, $this> */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(ShopPayment::class, 'payment_id');
    }

    protected function casts(): array
    {
        return ['response' => 'json'];
    }
}
