<?php

declare(strict_types=1);

namespace App\Actions\Shop\MyTravelCards;

use App\Models\Shop\ShopDigitalCard;

class GetAppleWalletCardFieldsAction
{
    /** @return array<string, array{value: string, label: string}> */
    public function handle(ShopDigitalCard $card): array
    {
        return [
            'card' => [
                'value' => $card->card_intro,
                'label' => $card->card_title,
            ],
            'cant_eat' => [
                'value' => $card->cant_eat_items,
                'label' => $card->cant_eat_label,
            ],
            'can_eat' => [
                'value' => $card->can_eat_items,
                'label' => $card->can_eat_label,
            ],
            'outro' => [
                'value' => "{$card->card_outro}\n\n{$card->card_thank_you}",
                'label' => '',
            ],
        ];
    }
}
