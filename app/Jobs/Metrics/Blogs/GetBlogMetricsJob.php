<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Blogs;

use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogMetric;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Jpeters8889\JourneyTrackerLaravel\Enums\EventType;
use Jpeters8889\JourneyTrackerLaravel\Facades\JourneyTracker;
use Jpeters8889\JourneyTrackerLaravel\Query\EventFilter;
use Jpeters8889\JourneyTrackerLaravel\Query\PageFilter;
use Jpeters8889\JourneyTrackerLaravel\Query\QueryDescriptor;
use Throwable;

class GetBlogMetricsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $backOff = 60;

    public int $tries = 1;

    public function __construct(protected Blog $blog)
    {
        $this->onQueue('metrics');
    }

    public function handle(): void
    {
        Log::info('GetBlogMetricsJob started', [
            'blog_id' => $this->blog->id,
        ]);

        try {
            $metric = JourneyTracker::query()
                ->today()
                ->count(
                    'views',
                    fn(QueryDescriptor $query) => $query
                        ->withPage(fn(PageFilter $page) => $page->path(mb_trim($this->blog->link, '/')))
                )
                ->count(
                    'comment_views',
                    fn(QueryDescriptor $query) => $query
                        ->withEvent(
                            fn(EventFilter $event) => $event
                                ->type(EventType::SCROLLED_INTO_VIEW)
                                ->identifier('CommentsCard')
                                ->withParameters([
                                    'page' => 'blog',
                                    'id' => $this->blog->id,
                                ])
                        )
                )
                ->count(
                    'detail_card_views',
                    fn(QueryDescriptor $query) => $query
                        ->withEvent(
                            fn(EventFilter $event) => $event
                                ->type(EventType::SCROLLED_INTO_VIEW)
                                ->identifier('BlogDetailCard')
                                ->withParameters([
                                    'title' => $this->blog->title,
                                ])
                        )
                )
                ->count(
                    'collection_card_views',
                    fn(QueryDescriptor $query) => $query
                        ->withEvent(
                            fn(EventFilter $event) => $event
                                ->type(EventType::SCROLLED_INTO_VIEW)
                                ->identifier('CollectionItemCard')
                                ->withParameters([
                                    'title' => $this->blog->title,
                                    'type' => 'Blog',
                                ])
                        )
                )
                ->get();
        } catch(Exception|RequestException $e) {
            Log::error('Failed to get blog metrics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'blog_id' => $this->blog->id,
            ]);

            throw $e;
        }

        BlogMetric::query()->upsert(
            [
                [
                    'blog_id' => $this->blog->id,
                    'date' => today()->toDateString(),
                    'page_views' => $metric->get('views'),
                    'page_comment_views' => $metric->get('comment_views'),
                    'detail_card_views' => $metric->get('detail_card_views'),
                    'collection_card_views' => $metric->get('collection_card_views'),
                ],
            ],
            ['blog_id', 'date'],
            ['page_views', 'page_comment_views', 'detail_card_views', 'collection_card_views']);
    }

    public function middleware(): array
    {
        return [new RateLimited('metrics')];
    }

    public function failed(?Throwable $e): void
    {
        Log::error('Failed to get blog metrics', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'blog_id' => $this->blog->id,
        ]);
    }
}
