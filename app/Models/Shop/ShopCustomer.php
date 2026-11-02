<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Enums\Shop\OrderState;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Notifications\Notifiable;

class ShopCustomer extends Model implements Authenticatable
{
    use \Illuminate\Auth\Authenticatable;
    use Notifiable;

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

    /** @return HasManyThrough<ShopOrderDigitalItem, ShopOrder, $this> */
    public function ownedDigitalItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            ShopOrderDigitalItem::class,
            ShopOrder::class,
            'customer_id',
            'order_id',
            'id',
            'id',
        )->whereIn('shop_orders.state_id', [OrderState::PAID, OrderState::READY, OrderState::SHIPPED]);
    }

    public function getAuthPassword(): string
    {
        return (string) $this->id;
    }

    protected function casts(): array
    {
        return [
            'one_time_code' => 'hashed',
            'one_time_code_expires_at' => 'datetime',
        ];
    }
}
