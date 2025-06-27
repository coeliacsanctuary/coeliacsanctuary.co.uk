<?php

declare(strict_types=1);

namespace App\Support\SiteMap;

use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class SiteMapGenerator
{
    /** @return Collection<int, Blog> */
    public function blogs(): Collection
    {
        return Cache::rememberForever(
            Config::string('coeliac.cacheable.blogs.site-map'),
            fn (): Collection => Blog::query()->latest()->get()
        );
    }

    /** @return Collection<int, Recipe> */
    public function recipes(): Collection
    {
        return Cache::rememberForever(
            Config::string('coeliac.cacheable.recipes.site-map'),
            fn (): Collection => Recipe::query()->latest()->get()
        );
    }

    /** @return Collection<int, EateryCounty> */
    public function counties(): Collection
    {
        return Cache::rememberForever(
            Config::string('coeliac.cacheable.eating-out.site-map-counties'),
            fn (): Collection => EateryCounty::query()
                ->where('county', '!=', 'Nationwide')
                ->orderBy('country_id')
                ->orderBy('county')
                ->get()
        );
    }

    /** @return Collection<int, EateryTown> */
    public function towns(): Collection
    {
        return Cache::rememberForever(
            Config::string('coeliac.cacheable.eating-out.site-map-towns'),
            fn (): Collection => EateryTown::query()
                ->where('slug', '!=', 'nationwide')
                ->with(['county'])
                ->orderBy('county_id')
                ->orderBy('town')
                ->get()
        );
    }

    /** @return Collection<int, EateryArea> */
    public function areas(): Collection
    {
        return Cache::rememberForever(
            Config::string('coeliac.cacheable.eating-out.site-map-areas'),
            fn (): Collection => EateryArea::query()
                ->with(['town'])
                ->orderBy('town_id')
                ->orderBy('area')
                ->get()
        );
    }

    /** @return Collection<int, Eatery> */
    public function eateries(): Collection
    {
        return Cache::rememberForever(
            Config::string('coeliac.cacheable.eating-out.site-map-eateries'),
            fn (): Collection => Eatery::query()
                ->where('county_id', '!=', 1)
                ->with(['county', 'town', 'area'])
                ->orderBy('county_id')
                ->orderBy('town_id')
                ->orderBy('area_id')
                ->orderBy('name')
                ->get()
        );
    }

    /** @return Collection<int, Eatery> */
    public function nationwideChains(): Collection
    {
        return Cache::rememberForever(
            Config::string('coeliac.cacheable.eating-out.site-map-nationwide'),
            fn (): Collection => Eatery::query()
                ->where('county_id', 1)
                ->with(['county'])
                ->orderBy('name')
                ->get()
        );
    }

    /** @return Collection<int, NationwideBranch> */
    public function nationwideBranches(): Collection
    {
        return Cache::rememberForever(
            Config::string('coeliac.cacheable.eating-out.site-map-nationwide-branches'),
            fn (): Collection => NationwideBranch::query()
                ->where('county_id', 1)
                ->with(['eatery'])
                ->orderBy('wheretoeat_id')
                ->orderBy('name')
                ->get()
        );
    }

    /** @return Collection<int, ShopCategory> */
    public function categories(): Collection
    {
        return Cache::rememberForever(
            Config::string('coeliac.cacheable.categories.site-map'),
            fn (): Collection => ShopCategory::query()->orderBy('title')->get()
        );
    }

    /** @return Collection<int, ShopProduct> */
    public function products(): Collection
    {
        return Cache::rememberForever(
            Config::string('coeliac.cacheable.products.site-map'),
            fn (): Collection => ShopProduct::query()->orderBy('title')->get()
        );
    }
}
