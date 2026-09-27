<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class ShopCustomer extends Model
{
    use Notifiable;

    protected $casts = [
        'one_time_code' => 'hashed',
        'one_time_code_expires_at' => 'datetime',
    ];

    /** @return HasMany<ShopShippingAddress, $this> */
    public function addresses(): HasMany
    {
        return $this->hasMany(ShopShippingAddress::class, 'customer_id');
    }

    /** @return HasMany<ShopOrder, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(ShopOrder::class, 'customer_id');
    }
}
