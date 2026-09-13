<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Enums\Shop\DigitalCardColor;
use App\Enums\Shop\DigitalCardIcon;
use App\Enums\Shop\DigitalCardType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShopDigitalCards extends Model
{
    protected $table = 'shop_digital_cards';

    protected $casts = [
        'live' => 'bool',
        'type' => DigitalCardType::class,
        'enable_physical_addons' => 'bool',
        'cant_eat_colour' => DigitalCardColor::class,
        'cant_eat_icon' => DigitalCardIcon::class,
        'can_eat_colour' => DigitalCardColor::class,
        'can_eat_icon' => DigitalCardIcon::class,
    ];

    public function price(): HasOne
    {
        return $this->hasOne(ShopDigitalCardPrice::class, 'digital_card_id');
    }

    /** @return BelongsTo<ShopProduct, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(ShopProduct::class, 'product_id');
    }

    /** @return HasManyThrough<TravelCardSearchTerm, ShopProduct, $this> */
    public function countries(): HasManyThrough
    {
        return $this->hasManyThrough(TravelCardSearchTerm::class, ShopProduct::class, 'id', 'product_id', 'product_id', 'id')
            ->wherePivot('card_show_on_product_page', true)
            ->whereColumn('shop_product_travel_cards_search_terms.card_language', 'shop_digital_cards.language');
    }
}
