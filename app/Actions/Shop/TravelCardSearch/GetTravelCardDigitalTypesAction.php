<?php

declare(strict_types=1);

namespace App\Actions\Shop\TravelCardSearch;

use App\Enums\Shop\DigitalCardType;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardType;
use App\Support\Helpers;
use Illuminate\Support\Collection;
use Money\Money;

class GetTravelCardDigitalTypesAction
{
    /** @return Collection<int, array{title: string, link: string, image: string, products_count: int<0, max>, price: string|null}> */
    public function handle(): Collection
    {
        return ShopDigitalCardType::query()
            ->with(['media', 'cards.price'])
            ->orderBy('id')
            ->get()
            ->map(fn (ShopDigitalCardType $type) => [
                'title' => $type->title,
                'link' => DigitalCardType::from($type->id)->route(),
                'image' => $type->main_image_as_webp ?? $type->main_image,
                'products_count' => $type->cards->count(),
                'price' => $this->cheapestPrice($type),
            ])
            ->values();
    }

    protected function cheapestPrice(ShopDigitalCardType $type): ?string
    {
        $cheapest = $type->cards
            ->map(fn (ShopDigitalCard $card) => ($card->price ?? ShopDigitalCard::defaultPrice())->price)
            ->min();

        if ( ! $cheapest) {
            return null;
        }

        return 'from ' . Helpers::formatMoney(Money::GBP((int) $cheapest));
    }
}
