<?php

declare(strict_types=1);

namespace Tests\Unit\Support\SiteMap;

use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Support\SiteMap\SiteMapGenerator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SiteMapGeneratorTest extends TestCase
{
    #[Test]
    public function itReturnsACollectionOfBlogs(): void
    {
        $this->build(Blog::class)->count(10)->state(['live' => true])->create();

        $this->assertInstanceOf(Collection::class, app(SiteMapGenerator::class)->blogs());

        app(SiteMapGenerator::class)->blogs()->each(function (Blog $blog): void {
            $this->assertInstanceOf(Blog::class, $blog);
        });
    }

    #[Test]
    public function itReturnsAllBlogs(): void
    {
        $this->build(Blog::class)->count(100)->state(['live' => true])->create();

        $this->assertCount(100, app(SiteMapGenerator::class)->blogs());
    }

    #[Test]
    public function itCachesTheBlogs(): void
    {
        $this->assertNull(Cache::get(config('coeliac.cacheable.blogs.site-map')));

        $this->build(Blog::class)->count(10)->state(['live' => true])->create();

        app(SiteMapGenerator::class)->blogs();

        $this->assertInstanceOf(Collection::class, Cache::get(config('coeliac.cacheable.blogs.site-map')));
    }

    #[Test]
    public function itReturnsACollectionOfRecipes(): void
    {
        $this->build(Recipe::class)->count(10)->state(['live' => true])->create();

        $this->assertInstanceOf(Collection::class, app(SiteMapGenerator::class)->recipes());

        app(SiteMapGenerator::class)->recipes()->each(function (Recipe $recipe): void {
            $this->assertInstanceOf(Recipe::class, $recipe);
        });
    }

    #[Test]
    public function itReturnsAllRecipes(): void
    {
        $this->build(Recipe::class)->count(100)->state(['live' => true])->create();

        $this->assertCount(100, app(SiteMapGenerator::class)->recipes());
    }

    #[Test]
    public function itCachesTheRecipes(): void
    {
        $this->assertNull(Cache::get(config('coeliac.cacheable.recipes.site-map')));

        $this->build(Recipe::class)->count(10)->state(['live' => true])->create();

        app(SiteMapGenerator::class)->recipes();

        $this->assertInstanceOf(Collection::class, Cache::get(config('coeliac.cacheable.recipes.site-map')));
    }

    #[Test]
    public function itReturnsACollectionOfCounties(): void
    {
        $this->build(EateryCounty::class)
            ->count(5)
            ->has($this->build(EateryTown::class)->has($this->build(Eatery::class)->state(['live' => true])), 'towns')
            ->state([])
            ->create();

        $this->assertInstanceOf(Collection::class, app(SiteMapGenerator::class)->counties());

        app(SiteMapGenerator::class)->counties()->each(function (EateryCounty $county): void {
            $this->assertInstanceOf(EateryCounty::class, $county);
        });
    }

    #[Test]
    public function itReturnsAllCounties(): void
    {
        $this->build(EateryCounty::class)
            ->count(20)
            ->has($this->build(EateryTown::class)->has($this->build(Eatery::class)->state(['live' => true])), 'towns')
            ->state([])
            ->create();

        $this->assertCount(20, app(SiteMapGenerator::class)->counties());
    }

    #[Test]
    public function itDoesntReturnTheNationwideCounty(): void
    {
        $this->build(EateryCounty::class)
            ->has($this->build(EateryTown::class)->has($this->build(Eatery::class)->state(['live' => true])), 'towns')
            ->state(['county' => 'Nationwide'])
            ->create();

        $this->assertEmpty(app(SiteMapGenerator::class)->counties());
    }

    #[Test]
    public function itCachesTheCounties(): void
    {
        $this->assertNull(Cache::get(config('coeliac.cacheable.eating-out.site-map-counties')));

        $this->build(EateryCounty::class)
            ->count(5)
            ->has($this->build(EateryTown::class)->has($this->build(Eatery::class)->state(['live' => true])), 'towns')
            ->state([])
            ->create();

        app(SiteMapGenerator::class)->counties();

        $this->assertInstanceOf(Collection::class, Cache::get(config('coeliac.cacheable.eating-out.site-map-counties')));
    }

    #[Test]
    public function itReturnsACollectionOfTowns(): void
    {
        $this->build(EateryTown::class)
            ->count(5)
            ->has($this->build(Eatery::class)->state(['live' => true]))
            ->state([])
            ->create();

        $this->assertInstanceOf(Collection::class, app(SiteMapGenerator::class)->towns());

        app(SiteMapGenerator::class)->towns()->each(function (EateryTown $town): void {
            $this->assertInstanceOf(EateryTown::class, $town);
        });
    }

    #[Test]
    public function itReturnsAllTowns(): void
    {
        $this->build(EateryTown::class)
            ->count(20)
            ->has($this->build(Eatery::class)->state(['live' => true]))
            ->state([])
            ->create();

        $this->assertCount(20, app(SiteMapGenerator::class)->towns());
    }

    #[Test]
    public function itDoesntReturnTheNationwideTown(): void
    {
        $this->build(EateryTown::class)
            ->has($this->build(Eatery::class)->state(['live' => true]))
            ->state(['slug' => 'nationwide'])
            ->create();

        $this->assertEmpty(app(SiteMapGenerator::class)->towns());
    }

    #[Test]
    public function itCachesTheTowns(): void
    {
        $this->assertNull(Cache::get(config('coeliac.cacheable.eating-out.site-map-towns')));

        $this->build(EateryTown::class)
            ->count(5)
            ->has($this->build(Eatery::class)->state(['live' => true]))
            ->state([])
            ->create();

        app(SiteMapGenerator::class)->towns();

        $this->assertInstanceOf(Collection::class, Cache::get(config('coeliac.cacheable.eating-out.site-map-towns')));
    }

    #[Test]
    public function itReturnsACollectionOfAreas(): void
    {
        $this->build(EateryArea::class)
            ->count(5)
            ->has($this->build(Eatery::class)->state(['live' => true]))
            ->state([])
            ->create();

        $this->assertInstanceOf(Collection::class, app(SiteMapGenerator::class)->areas());

        app(SiteMapGenerator::class)->areas()->each(function (EateryArea $area): void {
            $this->assertInstanceOf(EateryArea::class, $area);
        });
    }

    #[Test]
    public function itReturnsAllAreas(): void
    {
        $this->build(EateryArea::class)
            ->count(20)
            ->has($this->build(Eatery::class)->state(['live' => true]))
            ->state([])
            ->create();

        $this->assertCount(20, app(SiteMapGenerator::class)->areas());
    }

    #[Test]
    public function itCachesTheAreas(): void
    {
        $this->assertNull(Cache::get(config('coeliac.cacheable.eating-out.site-map-areas')));

        $this->build(EateryArea::class)
            ->count(5)
            ->has($this->build(Eatery::class)->state(['live' => true]))
            ->state([])
            ->create();

        app(SiteMapGenerator::class)->areas();

        $this->assertInstanceOf(Collection::class, Cache::get(config('coeliac.cacheable.eating-out.site-map-areas')));
    }

    #[Test]
    public function itReturnsACollectionOfEateries(): void
    {
        $county = $this->create(EateryCounty::class, ['id' => 2, 'county' => 'Foo']);

        $this->build(Eatery::class)
            ->count(5)
            ->state([
                'live' => true,
                'county_id' => $county->id
            ])
            ->create();

        $this->assertInstanceOf(Collection::class, app(SiteMapGenerator::class)->eateries());

        app(SiteMapGenerator::class)->eateries()->each(function (Eatery $eatery): void {
            $this->assertInstanceOf(Eatery::class, $eatery);
        });
    }

    #[Test]
    public function itReturnsAllEateries(): void
    {
        $county = $this->create(EateryCounty::class, ['id' => 2, 'county' => 'Foo']);

        $this->build(Eatery::class)
            ->count(100)
            ->state([
                'live' => true,
                'county_id' => $county->id
            ])
            ->create();

        $this->assertCount(100, app(SiteMapGenerator::class)->eateries());
    }

    #[Test]
    public function itDoesntReturnANationwideChain(): void
    {
        $county = $this->create(EateryCounty::class, ['id' => 1, 'county' => 'Nationwide']);

        $this->build(Eatery::class)
            ->state([
                'live' => true,
                'county_id' => $county->id
            ])
            ->create();

        $this->assertEmpty(app(SiteMapGenerator::class)->eateries());
    }

    #[Test]
    public function itCachesTheEateries(): void
    {
        $this->assertNull(Cache::get(config('coeliac.cacheable.eating-out.site-map-eateries')));

        $county = $this->create(EateryCounty::class, ['id' => 2, 'county' => 'Foo']);

        $this->build(Eatery::class)
            ->count(5)
            ->state([
                'live' => true,
                'county_id' => $county->id
            ])
            ->create();

        app(SiteMapGenerator::class)->eateries();

        $this->assertInstanceOf(Collection::class, Cache::get(config('coeliac.cacheable.eating-out.site-map-eateries')));
    }

    #[Test]
    public function itReturnsACollectionOfNationwideChains(): void
    {
        $county = $this->create(EateryCounty::class, ['id' => 1, 'county' => 'Nationwide']);

        $this->build(Eatery::class)
            ->count(5)
            ->state([
                'live' => true,
                'county_id' => $county->id
            ])
            ->create();

        $this->assertInstanceOf(Collection::class, app(SiteMapGenerator::class)->nationwideChains());

        app(SiteMapGenerator::class)->nationwideChains()->each(function (Eatery $eatery): void {
            $this->assertInstanceOf(Eatery::class, $eatery);
        });
    }

    #[Test]
    public function itReturnsAllNationwideChains(): void
    {
        $county = $this->create(EateryCounty::class, ['id' => 1, 'county' => 'Nationwide']);

        $this->build(Eatery::class)
            ->count(100)
            ->state([
                'live' => true,
                'county_id' => $county->id
            ])
            ->create();

        $this->assertCount(100, app(SiteMapGenerator::class)->nationwideChains());
    }

    #[Test]
    public function itDoesntReturnANormalEatery(): void
    {
        $county = $this->create(EateryCounty::class, ['id' => 2, 'county' => 'Foo']);

        $this->build(Eatery::class)
            ->state([
                'live' => true,
                'county_id' => $county->id
            ])
            ->create();

        $this->assertEmpty(app(SiteMapGenerator::class)->nationwideChains());
    }

    #[Test]
    public function itCachesTheNationwideChains(): void
    {
        $this->assertNull(Cache::get(config('coeliac.cacheable.eating-out.site-map-nationwide')));

        $county = $this->create(EateryCounty::class, ['id' => 1, 'county' => 'Nationwide']);

        $this->build(Eatery::class)
            ->count(5)
            ->state([
                'live' => true,
                'county_id' => $county->id
            ])
            ->create();

        app(SiteMapGenerator::class)->nationwideChains();

        $this->assertInstanceOf(Collection::class, Cache::get(config('coeliac.cacheable.eating-out.site-map-nationwide')));
    }

    #[Test]
    public function itReturnsACollectionOfNationwideBranches(): void
    {
        $county = $this->create(EateryCounty::class, ['id' => 1, 'county' => 'Nationwide']);
        $eatery = $this->create(Eatery::class, ['live' => true, 'county_id' => $county->id]);

        $this->build(NationwideBranch::class)
            ->count(5)
            ->state([
                'live' => true,
                'county_id' => $county->id,
                'wheretoeat_id' => $eatery->id,
            ])
            ->create();

        $this->assertInstanceOf(Collection::class, app(SiteMapGenerator::class)->nationwideBranches());

        app(SiteMapGenerator::class)->nationwideBranches()->each(function (NationwideBranch $branch): void {
            $this->assertInstanceOf(NationwideBranch::class, $branch);
        });
    }

    #[Test]
    public function itReturnsAllNationwideBranches(): void
    {
        $county = $this->create(EateryCounty::class, ['id' => 1, 'county' => 'Nationwide']);
        $eatery = $this->create(Eatery::class, ['live' => true, 'county_id' => $county->id]);

        $this->build(NationwideBranch::class)
            ->count(100)
            ->state([
                'live' => true,
                'county_id' => $county->id,
                'wheretoeat_id' => $eatery->id,
            ])
            ->create();

        $this->assertCount(100, app(SiteMapGenerator::class)->nationwideBranches());
    }

    #[Test]
    public function itCachesTheNationwideBranches(): void
    {
        $this->assertNull(Cache::get(config('coeliac.cacheable.eating-out.site-map-nationwide-branches')));

        $county = $this->create(EateryCounty::class, ['id' => 1, 'county' => 'Nationwide']);
        $eatery = $this->create(Eatery::class, ['live' => true, 'county_id' => $county->id]);

        $this->build(NationwideBranch::class)
            ->count(5)
            ->state([
                'live' => true,
                'county_id' => $county->id,
                'wheretoeat_id' => $eatery->id,
            ])
            ->create();

        app(SiteMapGenerator::class)->nationwideBranches();

        $this->assertInstanceOf(Collection::class, Cache::get(config('coeliac.cacheable.eating-out.site-map-nationwide-branches')));
    }

    #[Test]
    public function itReturnsACollectionOfShopCategories(): void
    {
        $this->build(ShopCategory::class)
            ->count(5)
            ->has($this->build(ShopProduct::class)->has($this->build(ShopProductVariant::class)->state(['live' => true]), 'variants'), 'products')
            ->state([])
            ->create();

        $this->assertInstanceOf(Collection::class, app(SiteMapGenerator::class)->categories());

        app(SiteMapGenerator::class)->categories()->each(function (ShopCategory $category): void {
            $this->assertInstanceOf(ShopCategory::class, $category);
        });
    }

    #[Test]
    public function itReturnsAllShopCategories(): void
    {
        $this->build(ShopCategory::class)
            ->count(15)
            ->has($this->build(ShopProduct::class)->has($this->build(ShopProductVariant::class)->state(['live' => true]), 'variants'), 'products')
            ->state([])
            ->create();

        $this->assertCount(15, app(SiteMapGenerator::class)->categories());
    }

    #[Test]
    public function itCachesTheShopCategories(): void
    {
        $this->assertNull(Cache::get(config('coeliac.cacheable.categories.site-map')));

        $this->build(ShopCategory::class)
            ->count(5)
            ->has($this->build(ShopProduct::class)->has($this->build(ShopProductVariant::class)->state(['live' => true]), 'variants'), 'products')
            ->state([])
            ->create();

        app(SiteMapGenerator::class)->categories();

        $this->assertInstanceOf(Collection::class, Cache::get(config('coeliac.cacheable.categories.site-map')));
    }

    #[Test]
    public function itReturnsACollectionOfShopProducts(): void
    {
        $this->build(ShopProduct::class)
            ->count(5)
            ->has($this->build(ShopProductVariant::class)->state(['live' => true]), 'variants')
            ->state([])
            ->create();

        $this->assertInstanceOf(Collection::class, app(SiteMapGenerator::class)->products());

        app(SiteMapGenerator::class)->products()->each(function (ShopProduct $product): void {
            $this->assertInstanceOf(ShopProduct::class, $product);
        });
    }

    #[Test]
    public function itReturnsAllShopProducts(): void
    {
        $this->build(ShopProduct::class)
            ->count(100)
            ->has($this->build(ShopProductVariant::class)->state(['live' => true]), 'variants')
            ->state([])
            ->create();

        $this->assertCount(100, app(SiteMapGenerator::class)->products());
    }

    #[Test]
    public function itCachesTheShopProducts(): void
    {
        $this->assertNull(Cache::get(config('coeliac.cacheable.products.site-map')));

        $this->build(ShopProduct::class)
            ->count(5)
            ->has($this->build(ShopProductVariant::class)->state(['live' => true]), 'variants')
            ->state([])
            ->create();

        app(SiteMapGenerator::class)->products();

        $this->assertInstanceOf(Collection::class, Cache::get(config('coeliac.cacheable.products.site-map')));
    }
}
