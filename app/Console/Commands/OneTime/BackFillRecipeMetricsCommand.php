<?php

declare(strict_types=1);

namespace App\Console\Commands\OneTime;

use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeMetric;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Jpeters8889\JourneyTrackerLaravel\Enums\EventType;
use Jpeters8889\JourneyTrackerLaravel\Facades\JourneyTracker;
use Jpeters8889\JourneyTrackerLaravel\Query\EventFilter;
use Jpeters8889\JourneyTrackerLaravel\Query\PageFilter;
use Jpeters8889\JourneyTrackerLaravel\Query\QueryDescriptor;

class BackFillRecipeMetricsCommand extends Command
{
    protected $signature = 'coeliac:one-time:back-fill-recipe-metrics';

    public function handle(): void
    {
        Recipe::query()
            ->latest()
            ->whereDoesntHave('metrics')
            ->lazy()
            ->each(function (Recipe $recipe): void {
                $this->info("Scheduling updates for {$recipe->title}");

                dispatch(function () use ($recipe): void {
                    $startDate = Carbon::parse('2026-02-24')->max($recipe->created_at);

                    $metrics = JourneyTracker::query()
                        ->from($startDate)
                        ->to(today())
                        ->daily()
                        ->count(
                            'views',
                            fn (QueryDescriptor $query) => $query
                                ->withPage(fn (PageFilter $page) => $page->path(mb_trim($recipe->link, '/')))
                        )
                        ->count(
                            'comment_views',
                            fn (QueryDescriptor $query) => $query
                                ->withEvent(
                                    fn (EventFilter $event) => $event
                                        ->type(EventType::SCROLLED_INTO_VIEW)
                                        ->identifier('CommentsCard')
                                        ->withParameters([
                                            'page' => 'recipe',
                                            'id' => $recipe->id,
                                        ])
                                )
                        )
                        ->count(
                            'detail_card_views',
                            fn (QueryDescriptor $query) => $query
                                ->withEvent(
                                    fn (EventFilter $event) => $event
                                        ->type(EventType::SCROLLED_INTO_VIEW)
                                        ->identifier('RecipeDetailCard')
                                        ->withParameters([
                                            'title' => $recipe->title,
                                        ])
                                )
                        )
                        ->count(
                            'collection_card_views',
                            fn (QueryDescriptor $query) => $query
                                ->withEvent(
                                    fn (EventFilter $event) => $event
                                        ->type(EventType::SCROLLED_INTO_VIEW)
                                        ->identifier('CollectionItemCard')
                                        ->withParameters([
                                            'title' => $recipe->title,
                                            'type' => 'Recipe',
                                        ])
                                )
                        )
                        ->get();

                    RecipeMetric::query()->upsert(
                        /** @phpstan-ignore-next-line */
                        collect($metrics->all())->map(fn (array $metric) => [
                            'recipe_id' => $recipe->id,
                            'date' => data_get($metric, 'date'),
                            'page_views' => data_get($metric, 'views'),
                            'page_comment_views' => data_get($metric, 'comment_views'),
                            'detail_card_views' => data_get($metric, 'detail_card_views'),
                            'collection_card_views' => data_get($metric, 'collection_card_views'),
                        ])->all(),
                        ['recipe_id', 'date'],
                        ['page_views', 'page_comment_views', 'detail_card_views', 'collection_card_views'],
                    );
                });
            });
    }
}
