<?php

declare(strict_types=1);

namespace App\Models\Shop;

use App\Builders\Shop\TravelCardSearchTermBuilder;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/** @property string $display_term */
#[UseEloquentBuilder(TravelCardSearchTermBuilder::class)]
class TravelCardSearchTerm extends Model
{
    protected $table = 'shop_product_travel_cards_search_terms';

    /** @return Attribute<string, never> */
    public function displayTerm(): Attribute
    {
        return Attribute::get(fn () => $this->term === Str::lower($this->term)
            ? Str::apa($this->term)
            : $this->term);
    }

    /** @return BelongsToMany<ShopProduct, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            ShopProduct::class,
            'shop_product_assigned_travel_card_search_terms',
            'search_term_id',
            'product_id',
        )->withTimestamps()->withPivot(['card_language', 'card_score', 'card_show_on_product_page']);
    }

    /** @return BelongsToMany<ShopDigitalCard, $this> */
    public function digitalCards(): BelongsToMany
    {
        return $this->belongsToMany(
            ShopDigitalCard::class,
            'shop_product_assigned_travel_card_search_terms',
            'search_term_id',
            'product_id',
            'id',
            'product_id',
        )->withPivot(['card_language']);
    }
}
