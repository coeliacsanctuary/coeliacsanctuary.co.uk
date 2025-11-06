<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\ShopCta;

use App\Models\Popup;

class GetController
{
    public function __invoke(): array
    {
        $popup = Popup::query()->first();

        return [
            'data' => [
                'text' => $popup->text,
                'link' => config('app.url') . $popup->link,
                'image' => $popup->getMedia('primary')->random()?->getUrl(),
            ],
        ];
    }
}
