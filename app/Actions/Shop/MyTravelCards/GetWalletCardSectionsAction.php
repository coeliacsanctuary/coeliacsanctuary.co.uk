<?php

declare(strict_types=1);

namespace App\Actions\Shop\MyTravelCards;

use App\DataObjects\Shop\WalletCardSection;
use App\Models\Shop\ShopDigitalCard;
use Illuminate\Support\Collection;

class GetWalletCardSectionsAction
{
    /** @return Collection<string, WalletCardSection> */
    public function handle(ShopDigitalCard $card): Collection
    {
        return Collection::make([
            new WalletCardSection('card', $card->card_title, $card->card_intro),
            new WalletCardSection('cant_eat', $card->cant_eat_label, $card->cant_eat_items),
            new WalletCardSection('can_eat', $card->can_eat_label, $card->can_eat_items),
            new WalletCardSection('outro', '', $card->card_outro),
            new WalletCardSection('thank_you', '', $card->card_thank_you),
        ])->keyBy(fn (WalletCardSection $section) => $section->key);
    }
}
