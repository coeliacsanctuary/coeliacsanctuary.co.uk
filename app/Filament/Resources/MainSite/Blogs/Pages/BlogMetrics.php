<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs\Pages;

use App\Filament\Resources\MainSite\Blogs\BlogResource;
use App\Filament\Resources\MainSite\Blogs\Widgets\CollectionCardViewsChart;
use App\Filament\Resources\MainSite\Blogs\Widgets\CommentViewsChart;
use App\Filament\Resources\MainSite\Blogs\Widgets\DetailCardViewsChart;
use App\Filament\Resources\MainSite\Blogs\Widgets\PageViewsChart;
use App\Models\Blogs\Blog;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class BlogMetrics extends Page
{
    use InteractsWithRecord;

    protected static string $resource = BlogResource::class;

    protected string $view = 'filament.pages.blog-metrics';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string|Htmlable
    {
        $blog = $this->getRecord();

        assert($blog instanceof Blog);

        return $blog->title;
    }

    /** @return array<int, class-string> */
    protected function getHeaderWidgets(): array
    {
        return [
            PageViewsChart::class,
            CommentViewsChart::class,
            DetailCardViewsChart::class,
            CollectionCardViewsChart::class,
        ];
    }
}
