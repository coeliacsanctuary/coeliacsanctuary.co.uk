<?php

declare(strict_types=1);

namespace App\Resources\Shop\MyTravelCards;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\TravelCardSearchTerm;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/** @mixin ShopDigitalCard */
class PurchasedCardIndexResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'slug' => $this->slug,
            'language' => $this->language,
            'link' => route('my-travel-cards.show', ['card' => $this]),
            'countries' => $this->whenLoaded('countries', fn (Collection $countries) => $countries->map(fn (TravelCardSearchTerm $searchTerm) => [
                'country' => ucwords($searchTerm->term),
                'code' => Helpers::countryCode($searchTerm->term),
            ])),
            'flag' => $this->whenLoaded('countries', fn (Collection $countries) => Helpers::countryCode($countries->first()->term)),
        ];
    }
}
