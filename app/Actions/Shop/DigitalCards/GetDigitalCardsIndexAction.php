<?php

declare(strict_types=1);

namespace App\Actions\Shop\DigitalCards;

use App\Enums\Shop\DigitalCardType;
use App\Models\Shop\ShopDigitalCard;
use Illuminate\Support\Collection;

class GetDigitalCardsIndexAction
{
    /** @return Collection<int, ShopDigitalCard> */
    public function handle(DigitalCardType $type): Collection
    {
        return ShopDigitalCard::query()
            ->where('type', $type)
            ->with(['price', 'product', 'product.variants', 'product.travelCardSearchTerms'])
            ->orderBy('title')
            ->get()
            ->map(function (ShopDigitalCard $digitalCard) {
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

                return $digitalCard;
            });
    }
}
