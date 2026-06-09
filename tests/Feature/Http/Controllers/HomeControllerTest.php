<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Actions\Blogs\GetTopBlogsForHomepageAction;
use App\Actions\Recipes\GetTopRecipesForHomepageAction;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\Blogs\GetLatestBlogsForHomepageAction;
use App\Actions\Collections\GetLatestCollectionsForHomepageAction;
use App\Actions\EatingOut\GetLatestEateriesForHomepageAction;
use App\Actions\EatingOut\GetLatestReviewsForHomepageAction;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Actions\Recipes\GetLatestRecipesForHomepageAction;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogMetric;
use App\Models\Collections\Collection;
use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeMetric;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    #[Test]
    public function itLoadsTheHomepage(): void
    {
        $this->get(route('home'))->assertOk();
    }

    #[Test]
    public function itCallsTheGetLatestBlogsForHomepageAction(): void
    {
        $this->expectAction(GetLatestBlogsForHomepageAction::class);

        $this->get(route('home'));
    }

    #[Test]
    public function itCallsTheGetTopBlogsForHomepageAction(): void
    {
        $this->expectAction(GetTopBlogsForHomepageAction::class);

        $this->get(route('home'));
    }

    #[Test]
    public function itCallsTheGetLatestRecipesForHomepageAction(): void
    {
        $this->expectAction(GetLatestRecipesForHomepageAction::class);

        $this->get(route('home'));
    }

    #[Test]
    public function itCallsTheGetTopRecipesForHomepageAction(): void
    {
        $this->expectAction(GetTopRecipesForHomepageAction::class);

        $this->get(route('home'));
    }

    #[Test]
    public function itCallsTheGetLatestCollectionsForHomepageAction(): void
    {
        $this->expectAction(GetLatestCollectionsForHomepageAction::class);

        $this->get(route('home'));
    }

    #[Test]
    public function itCallsTheGetLatestReviewsForHomepageAction(): void
    {
        $this->expectAction(GetLatestReviewsForHomepageAction::class);

        $this->get(route('home'));
    }

    #[Test]
    public function itCallsTheGetLatestEateriesForHomepageAction(): void
    {
        $this->expectAction(GetLatestEateriesForHomepageAction::class);

        $this->get(route('home'));
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageForRouteAction(): void
    {
        $this->expectAction(GetOpenGraphImageForRouteAction::class);

        $this->get(route('home'));
    }

    #[Test]
    public function itHasTheThreeLatestBlogs(): void
    {
        $this->withBlogs()
            ->get(route('home'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has(
                        'blogs.latest',
                        3,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'image', 'header_image_alt_text', 'link'])
                    )
                    ->where('blogs.latest.0.title', 'Blog 0')
                    ->where('blogs.latest.1.title', 'Blog 1')
                    ->etc()
            );
    }

    #[Test]
    public function itDoesntReturnBlogsThatArentLive(): void
    {
        $this->withBlogs(then: fn () => Blog::query()->first()->update(['live' => false]))
            ->get(route('home'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has(
                        'blogs.latest',
                        3,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'image', 'header_image_alt_text', 'link'])
                    )
                    ->where('blogs.latest.0.title', 'Blog 1')
                    ->where('blogs.latest.1.title', 'Blog 2')
                    ->etc()
            );
    }

    #[Test]
    public function itHasTheFourLatestRecipes(): void
    {
        $this->withRecipes()
            ->get(route('home'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has(
                        'recipes.latest',
                        4,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'image', 'header_image_alt_text', 'link'])
                    )
                    ->where('recipes.latest.0.title', 'Recipe 0')
                    ->where('recipes.latest.1.title', 'Recipe 1')
                    ->etc()
            );
    }

    #[Test]
    public function itDoesntReturnRecipesThatArentLive(): void
    {
        $this->withRecipes(then: fn () => Recipe::query()->first()->update(['live' => false]))
            ->get(route('home'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has(
                        'recipes.latest',
                        4,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'image', 'header_image_alt_text', 'link'])
                    )
                    ->where('recipes.latest.0.title', 'Recipe 1')
                    ->where('recipes.latest.1.title', 'Recipe 2')
                    ->etc()
            );
    }

    #[Test]
    public function itHasTheThreeTopBlogs(): void
    {
        $this->withBlogs(then: function (): void {
            $this->create(BlogMetric::class, ['blog_id' => 1, 'page_views' => 300]);
            $this->create(BlogMetric::class, ['blog_id' => 2, 'page_views' => 200]);
            $this->create(BlogMetric::class, ['blog_id' => 3, 'page_views' => 100]);
        })
            ->get(route('home'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has(
                        'blogs.top',
                        3,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'image', 'header_image_alt_text', 'link'])
                    )
                    ->where('blogs.top.0.title', 'Blog 0')
                    ->where('blogs.top.1.title', 'Blog 1')
                    ->etc()
            );
    }

    #[Test]
    public function itHasTheThreeTopRecipes(): void
    {
        $this->withRecipes(then: function (): void {
            $this->create(RecipeMetric::class, ['recipe_id' => 1, 'page_views' => 300]);
            $this->create(RecipeMetric::class, ['recipe_id' => 2, 'page_views' => 200]);
            $this->create(RecipeMetric::class, ['recipe_id' => 3, 'page_views' => 100]);
            $this->create(RecipeMetric::class, ['recipe_id' => 4, 'page_views' => 75]);
        })
            ->get(route('home'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has(
                        'recipes.top',
                        4,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'image', 'header_image_alt_text', 'link'])
                    )
                    ->where('recipes.top.0.title', 'Recipe 0')
                    ->where('recipes.top.1.title', 'Recipe 1')
                    ->etc()
            );
    }

    #[Test]
    public function itDisplaysTheCollections(): void
    {
        $this->withCollections(then: fn () => Collection::query()->first()->update(['display_on_homepage' => true]))
            ->get(route('home'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has(
                        'collections',
                        1,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'description', 'link', 'items', 'items_to_display'])
                    )
                    ->where('collections.0.title', 'Collection 0')
                    ->etc()
            );
    }
}
