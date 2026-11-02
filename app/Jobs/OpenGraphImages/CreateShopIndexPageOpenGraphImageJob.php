<?php

declare(strict_types=1);

namespace App\Jobs\OpenGraphImages;

use Illuminate\Foundation\Queue\Queueable;
use App\Models\OpenGraphImage;
use App\Models\Shop\ShopProduct;
use App\Services\RenderOpenGraphImage;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateShopIndexPageOpenGraphImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function handle(RenderOpenGraphImage $renderOpenGraphImage): void
    {
        if (config('coeliac.generate_og_images') === false) {
            return;
        }

        $spanishCard = ShopProduct::query()
            ->with(['media'])
            ->whereLike('title', 'spanish and italian%')
            ->firstOrFail();

        $otherAllergyCard = ShopProduct::query()
            ->with(['media'])
            ->whereLike('title', '%coeliac+%')
            ->firstOrFail();

        $base64Image = $renderOpenGraphImage->handle(view('og-images.shop', [
            'spanishCard' => $spanishCard,
            'otherAllergyCard' => $otherAllergyCard,
        ])->render());

        /** @var OpenGraphImage $openGraphModel */
        $openGraphModel = OpenGraphImage::query()->firstOrCreate(['route' => 'shop']);

        $openGraphModel->clearMediaCollection();
        $openGraphModel->addMediaFromBase64($base64Image)->usingFileName('og-image.png')->toMediaCollection();
        $openGraphModel->touch();
    }
}
