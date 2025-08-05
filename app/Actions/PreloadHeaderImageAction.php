<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\Recipes\Recipe;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;

class PreloadHeaderImageAction
{
    public function __construct(protected Router $router, protected Request $request)
    {
        //
    }

    public function handle(): ?string
    {
        $image = $this->resolveImage();

        if ( ! $image) {
            return null;
        }

        return "<link rel=\"preload\" as=\"image\" href=\"{$image}\" fetchpriority=\"high\" />";
    }

    protected function resolveImage(): ?string
    {
        if ($this->router->is('home')) {
            return '/images/travel-cards-hero.webp';
        }

        if ($this->router->is('blog.show')) {
            /** @var null | Blog | string $blog */
            $blog = $this->request->route('blog');

            if (is_string($blog)) {
                $blog = Blog::query()->where('slug', $blog)->first();
            }

            if ($blog) {
                return $blog->main_image;
            }
        }

        if ($this->router->is('recipe.show')) {
            /** @var null | Recipe | string $recipe */
            $recipe = $this->request->route('recipe');

            if (is_string($recipe)) {
                $recipe = Recipe::query()->where('slug', $recipe)->first();
            }

            if ($recipe) {
                return $recipe->main_image ?? $recipe->square_image;
            }
        }

        if ($this->router->is('collection.show')) {
            /** @var null | Collection | string $collection */
            $collection = $this->request->route('collection');

            if (is_string($collection)) {
                $collection = Collection::query()->where('slug', $collection)->first();
            }

            if ($collection) {
                return $collection->main_image;
            }
        }

        if ($this->router->is('eating-out.county')) {
            /** @var null | EateryCounty | string $county */
            $county = $this->request->route('county');

            if (is_string($county)) {
                $county = EateryCounty::query()->where('slug', $county)->first();
            }

            if ($county) {
                return $county->image ?? $county->country?->image;
            }
        }

        if ($this->router->is('eating-out.london')) {
            /** @var EateryCounty $county */
            $county = EateryCounty::query()->where('slug', 'london')->first();

            return $county->image ?? $county->country?->image;
        }

        if ($this->router->is('eating-out.town')) {
            /** @var null | EateryTown | string $town */
            $town = $this->request->route('town');

            if (is_string($town)) {
                $town = EateryTown::query()->where('slug', $town)->first();
            }

            if ($town) {
                return $town->image ?? $town->county->image ?? $town->county?->country?->image;
            }
        }

        if ($this->router->is('eating-out.london.borough')) {
            /** @var null | EateryTown | string $borough */
            $borough = $this->request->route('borough');

            if (is_string($borough)) {
                $borough = EateryTown::query()->where('slug', $borough)->first();
            }

            if ($borough) {
                return $borough->image ?? $borough->county?->image;
            }
        }

        if ($this->router->is('eating-out.london.borough.area')) {
            /** @var null | EateryArea | string $area */
            $area = $this->request->route('area');

            if (is_string($area)) {
                $area = EateryArea::query()->where('slug', $area)->first();
            }

            if ($area) {
                return $area->image ?? $area->town->image ?? $area->town?->county?->image;
            }
        }

        return null;
    }
}
