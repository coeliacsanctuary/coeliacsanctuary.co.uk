<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\Blogs\GetLatestBlogsForHomepageAction;
use App\Actions\Collections\GetLatestCollectionsForHomepageAction;
use App\Actions\EatingOut\GetLatestEateriesForHomepageAction;
use App\Actions\EatingOut\GetLatestReviewsForHomepageAction;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Actions\Recipes\GetLatestRecipesForHomepageAction;
use App\Models\Blogs\Blog;
use App\Models\Collections\Collection;
use App\Models\Recipes\Recipe;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HomepageTest extends TestCase
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
    public function itCallsTheGetLatestRecipesForHomepageAction(): void
    {
        $this->expectAction(GetLatestRecipesForHomepageAction::class);

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
    public function itHasTheSixLatestBlogs(): void
    {
        $this->withBlogs()
            ->get(route('home'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has(
                        'blogs',
                        6,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'image', 'link'])
                    )
                    ->where('blogs.0.title', 'Blog 0')
                    ->where('blogs.1.title', 'Blog 1')
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
                        'blogs',
                        6,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'image', 'link'])
                    )
                    ->where('blogs.0.title', 'Blog 1')
                    ->where('blogs.1.title', 'Blog 2')
                    ->etc()
            );
    }

    #[Test]
    public function itHasTheEightLatestRecipes(): void
    {
        $this->withRecipes()
            ->get(route('home'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Home')
                    ->has(
                        'recipes',
                        8,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'image', 'link'])
                    )
                    ->where('recipes.0.title', 'Recipe 0')
                    ->where('recipes.1.title', 'Recipe 1')
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
                        'recipes',
                        8,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'image', 'link'])
                    )
                    ->where('recipes.0.title', 'Recipe 1')
                    ->where('recipes.1.title', 'Recipe 2')
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
