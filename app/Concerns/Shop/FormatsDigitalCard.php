<?php

declare(strict_types=1);

namespace App\Concerns\Shop;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;

trait FormatsDigitalCard
{
    protected function formatDigitalCard(ShopDigitalCard $digitalCard, ?ShopOrder $basket = null): ShopDigitalCard
    {
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
            $basket->loadMissing('items');

            $digitalCard->setAttribute('physicalProductInBasket', $basket->items->where('product_id', $digitalCard->product_id)->isNotEmpty());
        }

        if ($basket) {
            $basket->loadMissing('digitalItems');

            $digitalCard->setAttribute('isInBasket', $basket->digitalItems->where('digital_card_id', $digitalCard->id)->isNotEmpty());
        }

        return $digitalCard;
    }
}
