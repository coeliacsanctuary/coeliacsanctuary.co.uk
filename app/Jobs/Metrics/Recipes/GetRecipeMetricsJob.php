<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Recipes;

use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeMetric;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Jpeters8889\JourneyTrackerLaravel\Enums\EventType;
use Jpeters8889\JourneyTrackerLaravel\Facades\JourneyTracker;
use Jpeters8889\JourneyTrackerLaravel\Query\EventFilter;
use Jpeters8889\JourneyTrackerLaravel\Query\PageFilter;
use Jpeters8889\JourneyTrackerLaravel\Query\QueryDescriptor;
use Throwable;

class GetRecipeMetricsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public function __construct(
        protected Recipe $recipe,
        public Carbon $date,
    ) {
        $this->onQueue('metrics');
    }

    public function handle(): void
    {
        Log::info('GetRecipeMetricsJob started', [
            'recipe_id' => $this->recipe->id,
        ]);

        try {
            $metric = JourneyTracker::query()
                ->today($this->date)
                ->count(
                    'views',
                    fn (QueryDescriptor $query) => $query
                        ->withPage(fn (PageFilter $page) => $page->path(mb_trim($this->recipe->link, '/')))
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
                                    'id' => $this->recipe->id,
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
                                    'title' => $this->recipe->title,
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
                                    'title' => $this->recipe->title,
                                    'type' => 'Recipe',
                                ])
                        )
                )
                ->get();
        } catch (Exception|RequestException $e) {
            Log::error('Failed to get recipe metrics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'blog_id' => $this->recipe->id,
            ]);

            throw $e;
        }

        RecipeMetric::query()->upsert(
            [
                [
                    'recipe_id' => $this->recipe->id,
                    'date' => $this->date->toDateString(),
                    'page_views' => $metric->get('views'),
                    'page_comment_views' => $metric->get('comment_views'),
                    'detail_card_views' => $metric->get('detail_card_views'),
                    'collection_card_views' => $metric->get('collection_card_views'),
                ],
            ],
            ['recipe_id', 'date'],
            ['page_views', 'page_comment_views', 'detail_card_views', 'collection_card_views']
        );
    }

    public function middleware(): array
    {
        return [];
    }

    public function failed(?Throwable $e): void
    {
        Log::error('Failed to get recipe metrics', [
            'error' => $e?->getMessage(),
            'trace' => $e?->getTraceAsString(),
            'recipe_id' => $this->recipe->id,
        ]);
    }
}
