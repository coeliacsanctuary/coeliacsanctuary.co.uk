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
            ->metaImage($cardType->social_image)
            ->metaDescription('Digital gluten free travel cards to help communicate your coeliac dietary needs when travelling abroad. Instant access on your phone wherever you travel.') // todo
            ->metaTags([
                'digital coeliac travel cards', 'digital gluten free travel cards', 'coeliac travel cards',
                'gluten free travel cards', 'coeliac translation cards', 'gluten free translation cards',
                'coeliac travel translation cards', 'gluten free travel translation cards', 'coeliac cards for travelling abroad',
                'gluten free cards for travelling abroad', 'coeliac dietary cards', 'gluten free dietary cards',
            ])
            ->render('Shop/DigitalCards', [
                'manageLink' => route('my-travel-cards.index'),
                'type' => $cardType->title,
                'categoryLink' => route('shop.category', $cardType->category()->firstOrFail()),
                'products' => fn () => ShopDigitalCardResource::collection($getDigitalCardsIndexAction->handle($cardType, $basket)),
            ]);
    }
}
