<?php

declare(strict_types=1);

namespace App\Nova\Chartables\Metrics\Blogs;

use App\Models\Blogs\BlogMetric as BlogMetricModel;
use Carbon\Carbon;
use Jpeters8889\ApexCharts\Chartable;

abstract class BlogMetric extends Chartable
{
    public function __construct(protected int $blogId)
    {
        //
    }

    abstract protected function column(): string;

    public function type(): string
    {
        return static::LINE_CHART;
    }

    public function getData(Carbon $startDate, Carbon $endDate): int
    {
        return BlogMetricModel::query()
            ->where('blog_id', $this->blogId)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->first()->{$this->column()} ?? 0;
    }

    public function defaultDateRange(): string
    {
        return self::DATE_RANGE_PAST_2_WEEKS;
    }

    protected function chartHeight(): string|int
    {
        return '200px';
    }

    protected function helpText(): ?string
    {
        $lastMetric = BlogMetricModel::query()
            ->where('blog_id', $this->blogId)
            ->latest()
            ->first();

        return $lastMetric ? 'Last updated: ' . $lastMetric->updated_at->format('Y-m-d H:i:s') : null;
    }
}
