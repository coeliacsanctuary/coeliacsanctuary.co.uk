<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\Blogs\Pages;

use App\Filament\Resources\MainSite\Blogs\Pages\BlogMetrics;
use App\Filament\Resources\MainSite\Blogs\Widgets\CollectionCardViewsChart;
use App\Filament\Resources\MainSite\Blogs\Widgets\CommentViewsChart;
use App\Filament\Resources\MainSite\Blogs\Widgets\DetailCardViewsChart;
use App\Filament\Resources\MainSite\Blogs\Widgets\PageViewsChart;
use App\Models\Blogs\Blog;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogMetricsTest extends TestCase
{
    protected Blog $blog;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->blog = $this->create(Blog::class, ['title' => 'How To Make Gluten Free Bread']);

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itLoadsTheMetricsPageForABlog(): void
    {
        Livewire::test(BlogMetrics::class, ['record' => $this->blog->getRouteKey()])->assertOk();
    }

    #[Test]
    public function itResolvesTheBlogFromTheRoute(): void
    {
        $page = Livewire::test(BlogMetrics::class, ['record' => $this->blog->getRouteKey()])->instance();

        $this->assertTrue($this->blog->is($page->getRecord()));
    }

    #[Test]
    public function itUsesTheBlogTitleAsThePageTitle(): void
    {
        $page = Livewire::test(BlogMetrics::class, ['record' => $this->blog->getRouteKey()])->instance();

        $this->assertSame('How To Make Gluten Free Bread', $page->getTitle());
    }

    #[Test]
    public function itShowsTheFourMetricCharts(): void
    {
        $page = Livewire::test(BlogMetrics::class, ['record' => $this->blog->getRouteKey()])->instance();

        $this->assertSame([
            PageViewsChart::class,
            CommentViewsChart::class,
            DetailCardViewsChart::class,
            CollectionCardViewsChart::class,
        ], (fn () => $this->getHeaderWidgets())->call($page));
    }

    #[Test]
    public function itPassesTheBlogToTheCharts(): void
    {
        $page = Livewire::test(BlogMetrics::class, ['record' => $this->blog->getRouteKey()])->instance();

        $this->assertTrue($this->blog->is($page->getWidgetData()['record']));
    }
}
