<?php

declare(strict_types=1);

namespace App\Console\Commands\OneTime;

use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogMetric;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Jpeters8889\JourneyTrackerLaravel\Enums\EventType;
use Jpeters8889\JourneyTrackerLaravel\Facades\JourneyTracker;
use Jpeters8889\JourneyTrackerLaravel\Query\EventFilter;
use Jpeters8889\JourneyTrackerLaravel\Query\PageFilter;
use Jpeters8889\JourneyTrackerLaravel\Query\QueryDescriptor;

class BackFillBlogMetricsCommand extends Command
{
    protected $signature = 'coeliac:one-time:back-fill-blog-metrics';

    public function handle(): void
    {
        Blog::query()
            ->latest()
//            ->whereDoesntHave('metrics')
            ->lazy()
            ->each(function (Blog $blog): void {
                $this->info("Scheduling updates for {$blog->title}");

                dispatch(function () use ($blog): void {
                    $startDate = Carbon::parse('2026-05-10')->max($blog->created_at);

                    $metrics = JourneyTracker::query()
                        ->from($startDate)
                        ->to(today())
                        ->daily()
                        ->count(
                            'views',
                            fn (QueryDescriptor $query) => $query
                                ->withPage(fn (PageFilter $page) => $page->path(mb_trim($blog->link, '/')))
                        )
                        ->count(
                            'comment_views',
                            fn (QueryDescriptor $query) => $query
                                ->withEvent(
                                    fn (EventFilter $event) => $event
                                        ->type(EventType::SCROLLED_INTO_VIEW)
                                        ->identifier('CommentsCard')
                                        ->withParameters([
                                            'page' => 'blog',
                                            'id' => $blog->id,
                                        ])
                                )
                        )
                        ->count(
                            'detail_card_views',
                            fn (QueryDescriptor $query) => $query
                                ->withEvent(
                                    fn (EventFilter $event) => $event
                                        ->type(EventType::SCROLLED_INTO_VIEW)
                                        ->identifier('BlogDetailCard')
                                        ->withParameters([
                                            'title' => $blog->title,
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
                                            'title' => $blog->title,
                                            'type' => 'Blog',
                                        ])
                                )
                        )
                        ->get();

                    BlogMetric::query()->upsert(
                        /** @phpstan-ignore-next-line */
                        collect($metrics->all())->map(fn (array $metric) => [
                            'blog_id' => $blog->id,
                            'date' => data_get($metric, 'date'),
                            'page_views' => data_get($metric, 'views'),
                            'page_comment_views' => data_get($metric, 'comment_views'),
                            'detail_card_views' => data_get($metric, 'detail_card_views'),
                            'collection_card_views' => data_get($metric, 'collection_card_views'),
                        ])->all(),
                        ['blog_id', 'date'],
                        ['page_views', 'page_comment_views', 'detail_card_views', 'collection_card_views'],
                    );
                });
            });
    }
}
