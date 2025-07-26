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
            /** @var ?Blog $blog */
            $blog = $this->request->route('blog');

            if ($blog) {
                return $blog->main_image;
            }
        }

        if ($this->router->is('recipe.show')) {
            /** @var ?Recipe $recipe */
            $recipe = $this->request->route('recipe');

            if ($recipe) {
                return $recipe->main_image ?? $recipe->square_image;
            }
        }

        if ($this->router->is('collection.show')) {
            /** @var ?Collection $collection */
            $collection = $this->request->route('collection');

            if ($collection) {
                return $collection->main_image;
            }
        }

        if ($this->router->is('eating-out.county')) {
            /** @var ?EateryCounty $county */
            $county = $this->request->route('county');

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
            /** @var ?EateryTown $town */
            $town = $this->request->route('town');

            if ($town) {
                return $town->image ?? $town->county->image ?? $town->county?->country?->image;
            }
        }

        if ($this->router->is('eating-out.london.borough')) {
            /** @var ?EateryTown $borough */
            $borough = $this->request->route('borough');

            if ($borough) {
                return $borough->image ?? $borough->county?->image;
            }
        }

        if ($this->router->is('eating-out.london.borough.area')) {
            /** @var ?EateryArea $area */
            $area = $this->request->route('area');


            if ($area) {
                return $area->image ?? $area->town->image ?? $area->town?->county?->image;
            }
        }

        return null;
    }
}
