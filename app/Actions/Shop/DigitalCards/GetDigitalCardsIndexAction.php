<?php

declare(strict_types=1);

namespace App\Actions\Shop\DigitalCards;

use App\Concerns\Shop\FormatsDigitalCard;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardType;
use App\Models\Shop\ShopOrder;
use Illuminate\Support\Collection;

class GetDigitalCardsIndexAction
{
    use FormatsDigitalCard;

    /** @return Collection<int, ShopDigitalCard> */
    public function handle(ShopDigitalCardType $type, ?ShopOrder $basket): Collection
    {
        $basket?->loadMissing('items');

        return ShopDigitalCard::query()
            ->where('type_id', $type->id)
            ->with(['price', 'product', 'product.variants', 'product.travelCardSearchTerms'])
            ->orderBy('title')
            ->get()
            ->map(fn (ShopDigitalCard $digitalCard) => $this->formatDigitalCard($digitalCard, $basket));
    }
}
