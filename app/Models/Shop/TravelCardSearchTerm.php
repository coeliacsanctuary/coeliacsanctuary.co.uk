<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TravelCardSearchTerm extends Model
{
    protected $table = 'shop_product_travel_cards_search_terms';

    /** @return BelongsToMany<ShopProduct, $this> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            ShopProduct::class,
            'shop_product_assigned_travel_card_search_terms',
            'search_term_id',
            'product_id',
        );
    }
}
