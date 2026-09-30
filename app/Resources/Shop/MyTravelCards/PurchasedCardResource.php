<?php

declare(strict_types=1);

namespace App\Resources\Shop\MyTravelCards;

use App\Models\Shop\ShopDigitalCard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ShopDigitalCard */
class PurchasedCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'language' => $this->language,
            'native_language' => $this->native_language,
            'content' => [
                'title' => $this->card_title,
                'intro' => $this->card_intro,
                'cant_eat' => [
                    'label' => $this->cant_eat_label,
                    'items' => $this->cant_eat_items,
                    'colour' => [
                        'hex' => $this->cant_eat_colour->color(),
                    ],
                    'icon' => $this->cant_eat_icon->icon(),
                ],
                'can_eat' => [
                    'label' => $this->can_eat_label,
                    'items' => $this->can_eat_items,
                    'colour' => [
                        'hex' => $this->can_eat_colour->color(),
                    ],
                    'icon' => $this->can_eat_icon->icon(),
                ],
                'outro' => $this->card_outro,
                'thank_you' => $this->card_thank_you,
            ],
        ];
    }
}
