<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Recipes\Pages;

use App\Filament\Resources\MainSite\Recipes\Pages\RecipeMetrics;
use App\Filament\Resources\MainSite\Recipes\Widgets\CollectionCardViewsChart;
use App\Filament\Resources\MainSite\Recipes\Widgets\CommentViewsChart;
use App\Filament\Resources\MainSite\Recipes\Widgets\DetailCardViewsChart;
use App\Filament\Resources\MainSite\Recipes\Widgets\PageViewsChart;
use App\Models\Recipes\Recipe;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecipeMetricsTest extends TestCase
{
    protected Recipe $recipe;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->recipe = $this->create(Recipe::class, ['title' => 'Gluten Free Victoria Sponge']);

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itLoadsTheMetricsPageForARecipe(): void
    {
        Livewire::test(RecipeMetrics::class, ['record' => $this->recipe->getRouteKey()])->assertOk();
    }

    #[Test]
    public function itResolvesTheRecipeFromTheRoute(): void
    {
        $page = Livewire::test(RecipeMetrics::class, ['record' => $this->recipe->getRouteKey()])->instance();

        $this->assertTrue($this->recipe->is($page->getRecord()));
    }

    #[Test]
    public function itUsesTheRecipeTitleAsThePageTitle(): void
    {
        $page = Livewire::test(RecipeMetrics::class, ['record' => $this->recipe->getRouteKey()])->instance();

        $this->assertSame('Gluten Free Victoria Sponge', $page->getTitle());
    }

    #[Test]
    public function itShowsTheFourMetricCharts(): void
    {
        $page = Livewire::test(RecipeMetrics::class, ['record' => $this->recipe->getRouteKey()])->instance();

        $this->assertSame([
            PageViewsChart::class,
            CommentViewsChart::class,
            DetailCardViewsChart::class,
            CollectionCardViewsChart::class,
        ], (fn () => $this->getHeaderWidgets())->call($page));
    }

    #[Test]
    public function itPassesTheRecipeToTheCharts(): void
    {
        $page = Livewire::test(RecipeMetrics::class, ['record' => $this->recipe->getRouteKey()])->instance();

        $this->assertTrue($this->recipe->is($page->getWidgetData()['record']));
    }
}
