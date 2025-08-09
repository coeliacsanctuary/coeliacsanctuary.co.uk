<?php

declare(strict_types=1);

namespace App\Jobs\OpenGraphImages;

use App\Models\OpenGraphImage;
use App\Models\Shop\ShopProduct;
use App\Services\RenderOpenGraphImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateShopIndexPageOpenGraphImageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 2;

    public function handle(RenderOpenGraphImage $renderOpenGraphImage): void
    {
        if (config('coeliac.generate_og_images') === false) {
            return;
        }

        $spanishCard = ShopProduct::query()
            ->with(['media'])
            ->where('title', 'like', 'spanish and italian%')
            ->firstOrFail();

//        $stickers = ShopProduct::query()
//            ->with(['media'])
//            ->where('title', 'like', '%stickers%')
//            ->firstOrFail();

        $otherAllergyCard = ShopProduct::query()
            ->with(['media'])
            ->where('title', 'like', '%coeliac+%')
            ->firstOrFail();

        $base64Image = $renderOpenGraphImage->handle(view('og-images.shop', [
            'spanishCard' => $spanishCard,
//            'stickers' => $stickers,
            'otherAllergyCard' => $otherAllergyCard,
        ])->render());

        /** @var OpenGraphImage $openGraphModel */
        $openGraphModel = OpenGraphImage::query()->firstOrCreate(['route' => 'shop']);

        $openGraphModel->clearMediaCollection();
        $openGraphModel->addMediaFromBase64($base64Image)->usingFileName('og-image.png')->toMediaCollection();
    }
}
