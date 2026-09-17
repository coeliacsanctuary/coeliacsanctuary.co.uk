<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\DigitalCards;

use App\Actions\Shop\DigitalCards\GetDigitalCardsIndexAction;
use App\Enums\Shop\DigitalCardType;
use App\Http\Response\Inertia;
use App\Resources\Shop\ShopDigitalCardResource;
use Illuminate\Http\Request;
use Inertia\Response;

class IndexController
{
    public function __invoke(Request $request, GetDigitalCardsIndexAction $getDigitalCardsIndexAction, Inertia $inertia): Response
    {
        $cardType = DigitalCardType::fromRequestPath($request->path());

        return $inertia
            ->title($cardType->pageTitle())
            ->render('Shop/DigitalCards', [
                'type' => $cardType->pageTitle(),
                'categoryLink' => route('shop.category', $cardType->category()),
                'products' => fn () => ShopDigitalCardResource::collection($getDigitalCardsIndexAction->handle($cardType)),
            ]);
    }
}
