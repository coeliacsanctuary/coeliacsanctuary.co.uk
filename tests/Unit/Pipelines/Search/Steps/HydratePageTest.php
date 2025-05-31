<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\Search\Steps;

use App\Contracts\Search\IsSearchable;
use App\DataObjects\Search\SearchResultItem;
use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use App\Models\Shop\ShopProduct;
use App\Pipelines\Search\Steps\HydratePage;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HydratePageTest extends TestCase
{
    #[Test]
    public function itHydratesABlogAndLoadsTheCorrectRelations(): void
    {
        /** @var Blog $blogModel */
        $blogModel = $this->create(Blog::class);

        $blog = new SearchResultItem(
            id: $blogModel->id,
            model: Blog::class,
            score: 123,
            firstWordPosition: 2
        );

        $paginator = collect([$blog])->paginate();

        $closure = function (LengthAwarePaginator $paginator) use ($blogModel): void {
            /** @var LengthAwarePaginator<int, IsSearchable> $results */
            $this->assertTrue($blogModel->is($paginator->first()));

            /** @var Blog $hydratedBlog */
            $hydratedBlog = $paginator->first();

            $this->assertTrue($hydratedBlog->relationLoaded('media'));
        };

        app(HydratePage::class)->handle($paginator, $closure);
    }

    #[Test]
    public function itHydratesARecipeAndLoadsTheCorrectRelations(): void
    {
        /** @var Recipe $recipeModel */
        $recipeModel = $this->create(Recipe::class);

        $recipe = new SearchResultItem(
            id: $recipeModel->id,
            model: Recipe::class,
            score: 123,
            firstWordPosition: 2
        );

        $paginator = collect([$recipe])->paginate();

        $closure = function (LengthAwarePaginator $paginator) use ($recipeModel): void {
            /** @var LengthAwarePaginator<int, IsSearchable> $results */
            $this->assertTrue($recipeModel->is($paginator->first()));

            /** @var Recipe $hydratedRecipe */
            $hydratedRecipe = $paginator->first();

            $this->assertTrue($hydratedRecipe->relationLoaded('media'));
        };

        app(HydratePage::class)->handle($paginator, $closure);
    }

    #[Test]
    public function itHydratesAnEateryAndLoadsTheCorrectRelations(): void
    {
        /** @var Eatery $eateryModel */
        $eateryModel = $this->create(Eatery::class);

        $eatery = new SearchResultItem(
            id: $eateryModel->id,
            model: Eatery::class,
            score: 123,
            firstWordPosition: 2
        );

        $paginator = collect([$eatery])->paginate();

        $closure = function (LengthAwarePaginator $paginator) use ($eateryModel): void {
            /** @var LengthAwarePaginator<int, IsSearchable> $results */
            $this->assertTrue($eateryModel->is($paginator->first()));

            /** @var Eatery $hydratedEatery */
            $hydratedEatery = $paginator->first();

            $this->assertTrue($hydratedEatery->relationLoaded('country'));
            $this->assertTrue($hydratedEatery->relationLoaded('county'));
            $this->assertTrue($hydratedEatery->relationLoaded('town'));
        };

        app(HydratePage::class)->handle($paginator, $closure);
    }

    #[Test]
    public function itHydratesANationwideBranchAndLoadsTheCorrectRelations(): void
    {
        /** @var NationwideBranch $nationwideBranchModel */
        $nationwideBranchModel = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->create(Eatery::class),
        ]);

        $nationwideBranch = new SearchResultItem(
            id: $nationwideBranchModel->id,
            model: NationwideBranch::class,
            score: 123,
            firstWordPosition: 2
        );

        $paginator = collect([$nationwideBranch])->paginate();

        $closure = function (LengthAwarePaginator $paginator) use ($nationwideBranchModel): void {
            /** @var LengthAwarePaginator<int, IsSearchable> $results */
            $this->assertTrue($nationwideBranchModel->is($paginator->first()));

            /** @var NationwideBranch $hydratedNationwideBranch */
            $hydratedNationwideBranch = $paginator->first();

            $this->assertTrue($hydratedNationwideBranch->relationLoaded('country'));
            $this->assertTrue($hydratedNationwideBranch->relationLoaded('county'));
            $this->assertTrue($hydratedNationwideBranch->relationLoaded('town'));
            $this->assertTrue($hydratedNationwideBranch->relationLoaded('eatery'));
        };

        app(HydratePage::class)->handle($paginator, $closure);
    }

    #[Test]
    public function itHydratesAShopProductAndLoadsTheCorrectRelations(): void
    {
        $this->withCategoriesAndProducts(1, 1);

        /** @var ShopProduct $shopProductModel */
        $shopProductModel = ShopProduct::query()->first();

        $shopProduct = new SearchResultItem(
            id: $shopProductModel->id,
            model: ShopProduct::class,
            score: 123,
            firstWordPosition: 2
        );

        $paginator = collect([$shopProduct])->paginate();

        $closure = function (LengthAwarePaginator $paginator) use ($shopProductModel): void {
            /** @var LengthAwarePaginator<int, IsSearchable> $results */
            $this->assertTrue($shopProductModel->is($paginator->first()));

            /** @var ShopProduct $hydratedShopProduct */
            $hydratedShopProduct = $paginator->first();

            $this->assertTrue($hydratedShopProduct->relationLoaded('variants'));
            $this->assertTrue($hydratedShopProduct->relationLoaded('prices'));
        };

        app(HydratePage::class)->handle($paginator, $closure);
    }
}
