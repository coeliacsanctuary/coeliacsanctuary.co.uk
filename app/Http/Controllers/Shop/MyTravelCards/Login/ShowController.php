<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\MyTravelCards\Login;

use App\Http\Response\Inertia;
use Inertia\Response;

class ShowController
{
    public function __invoke(Inertia $inertia): Response
    {
        return $inertia
            ->doNotTrack()
            ->metaTags([], false)
            ->metaDescription('')
            ->title('Login - My Travel Cards')
            ->render('Shop/MyTravelCards/Login');
    }
}
