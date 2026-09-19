<?php

declare(strict_types=1);

namespace App\Actions\Shop\DigitalCards;

use App\Enums\Shop\DigitalCardType;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use Illuminate\Support\Collection;

class GetDigitalCardsIndexAction
{
    /** @return Collection<int, ShopDigitalCard> */
    public function handle(DigitalCardType $type, ?ShopOrder $basket): Collection
    {
        $basket?->loadMissing('items');

        return ShopDigitalCard::query()
            ->where('type', $type)
            ->with(['price', 'product', 'product.variants', 'product.travelCardSearchTerms'])
            ->orderBy('title')
            ->get()
            ->map(function (ShopDigitalCard $digitalCard) use ($basket) {
                if ( ! $digitalCard->price) {
                    $digitalCard->setRelation('price', ShopDigitalCard::defaultPrice());
                }

                if ($digitalCard->product?->travelCardSearchTerms?->isNotEmpty()) {
                    $digitalCard->setRelation(
                        'countries',
                        $digitalCard->product
                            ->travelCardSearchTerms
                            ->where('type', 'country')
                            ->where('pivot.card_language', $digitalCard->language)
                            ->where('pivot.card_show_on_product_page', true)
                            ->sortByDesc('pivot.card_score')
                            ->values()
                    );
                }

                if ($basket && $digitalCard->product) {
                    $digitalCard->setAttribute('physicalProductInBasket', $basket->items->where('product_id', $digitalCard->product_id)->isNotEmpty());
                }

                return $digitalCard;
            });
    }
}
