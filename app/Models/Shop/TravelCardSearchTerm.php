<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/** @property string $display_term */
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

    /**
     * @param  Builder<$this> $query
     * @return Builder<$this>
     */
    public function scopeMatching(Builder $query, string $term): Builder
    {
        return $query
            ->where('term', 'like', '%' . addcslashes($term, '%_\\') . '%')
            ->orderByRaw('term = ? desc', [$term])
            ->orderByRaw('locate(?, term) asc', [$term])
            ->orderByRaw('char_length(term) asc');
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
}
