<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\MyTravelCards\Login\Confirm;

use App\Http\Response\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Response;

class ShowController
{
    public function __invoke(Inertia $inertia, Request $request): Response
    {
        $email = $request->cookie('my_travel_cards_email');

        return $inertia
            ->doNotTrack()
            ->metaTags([], false)
            ->metaDescription('')
            ->title('Confirm - My Travel Cards')
            ->render('Shop/MyTravelCards/ConfirmLogin', [
                'email' => Crypt::decryptString($email),
            ]);
    }
}
