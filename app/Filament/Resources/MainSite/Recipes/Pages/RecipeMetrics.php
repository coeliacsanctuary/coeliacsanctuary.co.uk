<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Recipes\Pages;

use App\Filament\Resources\MainSite\Recipes\RecipeResource;
use App\Filament\Resources\MainSite\Recipes\Widgets\CollectionCardViewsChart;
use App\Filament\Resources\MainSite\Recipes\Widgets\CommentViewsChart;
use App\Filament\Resources\MainSite\Recipes\Widgets\DetailCardViewsChart;
use App\Filament\Resources\MainSite\Recipes\Widgets\PageViewsChart;
use App\Models\Recipes\Recipe;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class RecipeMetrics extends Page
{
    use InteractsWithRecord;

    protected static string $resource = RecipeResource::class;

    protected string $view = 'filament.pages.recipe-metrics';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string|Htmlable
    {
        $recipe = $this->getRecord();

        assert($recipe instanceof Recipe);

        return $recipe->title;
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
