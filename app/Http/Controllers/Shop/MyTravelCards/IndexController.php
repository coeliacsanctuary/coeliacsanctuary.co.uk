<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\MyTravelCards;

use App\Concerns\Shop\FormatsDigitalCard;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Resources\Shop\MyTravelCards\PurchasedCardIndexResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Inertia\Response;

class IndexController
{
    use FormatsDigitalCard;

    public function __invoke(Inertia $inertia, Request $request): Response
    {
        $user = $request->user();

        if ( ! $user instanceof ShopCustomer) {
            abort(IlluminateResponse::HTTP_FORBIDDEN, 'You must be logged in to view this page.');
        }

        $cards = $user
            ->ownedDigitalItems
            ->load([
                'card' => fn (Relation $relation) => $relation->withoutGlobalScopes(),
                'card.price',
                'card.product' => fn (Relation $relation) => $relation->withoutGlobalScopes(),
                'card.product.travelCardSearchTerms',
            ])
            ->unique('digital_card_id')
            ->values()
            ->map(fn (ShopOrderDigitalItem $item) => $this->formatDigitalCard($item->card))
            ->sortBy('title')
            ->values()
            ->mapInto(PurchasedCardIndexResource::class);

        return $inertia
            ->doNotTrack()
            ->metaTags([], false)
            ->metaDescription('')
            ->title('My Travel Cards')
            ->render('Shop/MyTravelCards/Index', [
                'cards' => $cards,
            ]);
    }
}
