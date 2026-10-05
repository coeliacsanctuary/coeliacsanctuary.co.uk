<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\DigitalCards;

use App\Actions\Shop\DigitalCards\GetDigitalCardsIndexAction;
use App\Actions\Shop\ResolveBasketAction;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopDigitalCardType;
use App\Resources\Shop\ShopDigitalCardResource;
use Illuminate\Http\Request;
use Inertia\Response;

class IndexController
{
    public function __invoke(
        Request $request,
        ResolveBasketAction $resolveBasketAction,
        GetDigitalCardsIndexAction $getDigitalCardsIndexAction,
        Inertia $inertia,
    ): Response {
        /** @var string | null $token */
        $token = $request->cookies->get('basket_token');

        $basket = $resolveBasketAction->handle($token, create: false);
        $basket?->loadMissing('items');

        $cardType = ShopDigitalCardType::fromRequestPath($request->path());

        return $inertia
            ->title($cardType->title)
            ->render('Shop/DigitalCards', [
                'manageLink' => route('my-travel-cards.index'),
                'type' => $cardType->title,
                'categoryLink' => route('shop.category', $cardType->category()->firstOrFail()),
                'products' => fn () => ShopDigitalCardResource::collection($getDigitalCardsIndexAction->handle($cardType, $basket)),
            ]);
    }
}
