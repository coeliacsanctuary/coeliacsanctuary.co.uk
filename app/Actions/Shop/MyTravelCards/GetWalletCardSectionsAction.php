<?php

declare(strict_types=1);

namespace App\Actions\Shop\MyTravelCards;

use App\DataObjects\Shop\WalletCardSection;
use App\Models\Shop\ShopDigitalCard;

class GetWalletCardSectionsAction
{
    /**
     * @return array{
     *     card: WalletCardSection,
     *     cant_eat: WalletCardSection,
     *     can_eat: WalletCardSection,
     *     outro: WalletCardSection,
     *     thank_you: WalletCardSection,
     * }
     */
    public function handle(ShopDigitalCard $card): array
    {
        return [
            'card' => new WalletCardSection('card', $card->card_title, $card->card_intro),
            'cant_eat' => new WalletCardSection('cant_eat', $card->cant_eat_label, $card->cant_eat_items),
            'can_eat' => new WalletCardSection('can_eat', $card->can_eat_label, $card->can_eat_items),
            'outro' => new WalletCardSection('outro', '', $card->card_outro),
            'thank_you' => new WalletCardSection('thank_you', '', $card->card_thank_you),
        ];
    }
}
