<?php

declare(strict_types=1);

namespace App\Actions\OpenGraphImages;

use App\Jobs\OpenGraphImages\CreateBlogIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateCollectionIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryAppPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateHomePageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateRecipeIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateShopIndexPageOpenGraphImageJob;
use App\Models\OpenGraphImage;

class GetOpenGraphImageForRouteAction
{
    /** @param callable(string, OpenGraphImage): string $alterUrl */
    public function handle(string $route = 'home', ?callable $alterUrl = null): string
    {
        /** @var OpenGraphImage | null $model */
        $model = OpenGraphImage::query()
            ->with(['media'])
            ->where('route', $route)
            ->where('updated_at', '>=', now()->subHours(24))
            ->first();

        if ($model && $model->image_url) {
            $url = $model->image_url;

            if ($alterUrl) {
                $url = $alterUrl($url, $model);
            }

            return $url;
        }

        match ($route) {
            'blog' => CreateBlogIndexPageOpenGraphImageJob::dispatch(),
            'recipe' => CreateRecipeIndexPageOpenGraphImageJob::dispatch(),
            'collection' => CreateCollectionIndexPageOpenGraphImageJob::dispatch(),
            'shop' => CreateShopIndexPageOpenGraphImageJob::dispatch(),
            'eatery', 'eatery-map' => CreateEateryIndexPageOpenGraphImageJob::dispatch(),
            'eatery-app' => CreateEateryAppPageOpenGraphImageJob::dispatch(),
            default => CreateHomePageOpenGraphImageJob::dispatch(),
        };

        return '';
    }
}
