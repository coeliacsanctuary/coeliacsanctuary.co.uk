<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Actions\Shop\MyTravelCards\SyncGoogleWalletClassAction;
use App\Enums\Shop\DigitalCardColor;
use App\Enums\Shop\DigitalCardIcon;
use App\Enums\Shop\DigitalCardType;
use App\Scopes\LiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ShopDigitalCard extends Model
{
    protected $casts = [
        'live' => 'bool',
        'type_id' => DigitalCardType::class,
        'enable_physical_addons' => 'bool',
        'cant_eat_colour' => DigitalCardColor::class,
        'cant_eat_icon' => DigitalCardIcon::class,
        'can_eat_colour' => DigitalCardColor::class,
        'can_eat_icon' => DigitalCardIcon::class,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new LiveScope());

        static::saved(function (self $card): void {
            if ( ! config()->boolean('coeliac.google_wallet_enabled')) {
                return;
            }

            if ($card->live && $card->google_wallet_class_id === null) {
                dispatch(fn () => app(SyncGoogleWalletClassAction::class)->handle($card));
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)->withoutGlobalScopes();
    }

    /** @return HasOne<ShopDigitalCardPrice, $this> */
    public function price(): HasOne
    {
        return $this->hasOne(ShopDigitalCardPrice::class, 'digital_card_id');
    }

    public static function defaultPrice(): ShopDigitalCardPrice
    {
        return once(fn () => ShopDigitalCardPrice::query()->where('digital_card_id', 0)->firstOrFail());
    }

    /** @return BelongsTo<ShopProduct, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }

    /** @return BelongsTo<ShopDigitalCardType, $this> */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ShopDigitalCardType::class, 'type_id');
    }

    /** @return HasMany<ShopOrderDigitalItem, $this> */
    public function orderDigitalItems(): HasMany
    {
        return $this->hasMany(ShopOrderDigitalItem::class, 'digital_card_id');
    }

    /** @return MorphMany<ShopOrderReviewItem, $this> */
    public function reviews(): MorphMany
    {
        return $this
            ->morphMany(ShopOrderReviewItem::class, 'product')
            ->whereIn('shop_order_review_items.id', ShopOrderReviewItem::deduplicatedIds());
    }
}
