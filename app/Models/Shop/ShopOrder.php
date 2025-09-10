<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Enums\Shop\OrderState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Str;

class ShopOrder extends Model
{
    protected $casts = [
        'state_id' => OrderState::class,
        'shipped_at' => 'datetime',
        'digital_products_sent_at' => 'datetime',
        'sent_abandoned_basket_email' => 'boolean',
        'has_digital_products' => 'boolean',
        'is_digital_only' => 'boolean',
    ];

    protected static function booted(): void
    {
        self::creating(function (self $order): void {
            if ($order->state_id === null) {
                $order->state_id = OrderState::BASKET;
            }

            if ( ! $order->postage_country_id) {
                $order->postage_country_id = 1;
            }

            $order->token = Str::random(8);
        });
    }

    /** @return BelongsTo<ShopOrderState, $this> */
    public function state(): BelongsTo
    {
        return $this->belongsTo(ShopOrderState::class, 'state_id');
    }

    /** @return BelongsTo<ShopCustomer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(ShopCustomer::class);
    }

    /** @return BelongsTo<ShopShippingAddress, $this> */
    public function address(): BelongsTo
    {
        return $this->belongsTo(ShopShippingAddress::class, 'shipping_address_id');
    }

    /** @return HasOne<ShopPayment, $this> */
    public function payment(): HasOne
    {
        return $this->hasOne(ShopPayment::class, 'order_id');
    }

    /** @return HasMany<ShopPaymentRefund, $this> */
    public function refunds(): HasMany
    {
        return $this->hasMany(ShopPaymentRefund::class, 'order_id');
    }

    /** @return HasMany<ShopOrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ShopOrderItem::class, 'order_id');
    }

    /** @return BelongsTo<ShopPostageCountry, $this> */
    public function postageCountry(): BelongsTo
    {
        return $this->belongsTo(ShopPostageCountry::class, 'postage_country_id');
    }

    /** @return HasOneThrough<ShopDiscountCode, ShopDiscountCodesUsed, $this> */
    public function discountCode(): HasOneThrough
    {
        return $this->hasOneThrough(ShopDiscountCode::class, ShopDiscountCodesUsed::class, 'order_id', 'id', 'id', 'discount_id');
    }

    /** @return HasOne<ShopOrderReviewInvitation, $this> */
    public function reviewInvitation(): HasOne
    {
        return $this->hasOne(ShopOrderReviewInvitation::class, 'order_id');
    }

    /** @return HasMany<ShopOrderReview, $this> */
    public function reviews(): HasMany
    {
        return $this->hasMany(ShopOrderReview::class, 'order_id');
    }

    /** @return HasMany<ShopOrderReviewItem, $this> */
    public function reviewedItems(): HasMany
    {
        return $this->hasMany(ShopOrderReviewItem::class, 'order_id');
    }

    /** @return BelongsToMany<ShopSource, $this> */
    public function sources(): BelongsToMany
    {
        return $this->belongsToMany(ShopSource::class, 'shop_order_sources', 'order_id', 'source_id');
    }

    /** @return HasMany<ShopOrderDownloadLink, $this> */
    public function downloadLinks(): HasMany
    {
        return $this->hasMany(ShopOrderDownloadLink::class, 'order_id');
    }
}
