<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Enums\Shop\DigitalCardColor;
use App\Enums\Shop\DigitalCardIcon;
use App\Enums\Shop\DigitalCardType;
use App\Scopes\LiveScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShopDigitalCard extends Model
{
    protected $casts = [
        'live' => 'bool',
        'type' => DigitalCardType::class,
        'enable_physical_addons' => 'bool',
        'cant_eat_colour' => DigitalCardColor::class,
        'cant_eat_icon' => DigitalCardIcon::class,
        'can_eat_colour' => DigitalCardColor::class,
        'can_eat_icon' => DigitalCardIcon::class,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new LiveScope());
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
}
