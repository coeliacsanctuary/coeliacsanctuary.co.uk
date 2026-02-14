<?php

declare(strict_types=1);

namespace App\Jobs\OpenGraphImages;

use App\Models\OpenGraphImage;
use App\Models\Recipes\Recipe;
use App\Services\RenderOpenGraphImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateRecipeIndexPageOpenGraphImageJob implements ShouldQueue
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

        $recipes = Recipe::query()->with(['media'])->latest()->take(4)->get();

        $base64Image = $renderOpenGraphImage->handle(view('og-images.recipe', [
            'recipes' => $recipes,
        ])->render());

        /** @var OpenGraphImage $openGraphModel */
        $openGraphModel = OpenGraphImage::query()->firstOrCreate(['route' => 'recipe']);

        $openGraphModel->clearMediaCollection();
        $openGraphModel->addMediaFromBase64($base64Image)->usingFileName('og-image.png')->toMediaCollection();
        $openGraphModel->touch();
    }
}
