<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Sources;

use App\Contracts\Metrics\MetricSource;
use App\Jobs\Metrics\Blogs\GetBlogMetricsJob;
use App\Models\Blogs\Blog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class BlogMetricSource implements MetricSource
{
    public function __construct(
        protected ?Carbon $createdAfter = null,
        protected ?Carbon $createdBefore = null,
    ) {
    }

    /** @return Builder<Model> */
    public function query(): Builder
    {
        $query = Blog::query()->latest();

        if ($this->createdAfter !== null) {
            $query->where('created_at', '>=', $this->createdAfter);
        }

        if ($this->createdBefore !== null) {
            $query->where('created_at', '<=', $this->createdBefore);
        }

        /** @phpstan-ignore return.type */
        return $query;
    }

    public function metricsRelation(): string
    {
        return 'metrics';
    }

    public function dispatch(Model $model, int $delaySeconds): void
    {
        assert($model instanceof Blog);
        GetBlogMetricsJob::dispatch($model)->delay($delaySeconds);
    }
}
