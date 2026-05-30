<?php

declare(strict_types=1);

namespace App\Actions\OpenGraphImages;

use App\Jobs\OpenGraphImages\CreateBlogIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateCollectionIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryAppPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateEateryMapPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateHomePageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateRecipeIndexPageOpenGraphImageJob;
use App\Jobs\OpenGraphImages\CreateShopIndexPageOpenGraphImageJob;
use App\Models\OpenGraphImage;

class GetOpenGraphImageForRouteAction
{
    public function handle(string $route = 'home'): string
    {
        /** @var OpenGraphImage | null $model */
        $model = OpenGraphImage::query()
            ->with(['media'])
            ->where('route', $route)
            ->where('updated_at', '>=', now()->subHours(24))
            ->first();

        if ($model && $model->image_url) {
            return $model->image_url;
        }

        match ($route) {
            'blog' => CreateBlogIndexPageOpenGraphImageJob::dispatch(),
            'recipe' => CreateRecipeIndexPageOpenGraphImageJob::dispatch(),
            'collection' => CreateCollectionIndexPageOpenGraphImageJob::dispatch(),
            'shop' => CreateShopIndexPageOpenGraphImageJob::dispatch(),
            'eatery' => CreateEateryIndexPageOpenGraphImageJob::dispatch(),
            'eatery-app' => CreateEateryAppPageOpenGraphImageJob::dispatch(),
            'eatery-map' => CreateEateryMapPageOpenGraphImageJob::dispatch(),
            default => CreateHomePageOpenGraphImageJob::dispatch(),
        };

        return '';
    }
}
