<?php

declare(strict_types=1);

namespace App\Nova\Chartables\Metrics\Recipes;

use App\Models\Recipes\RecipeMetric as RecipeMetricModel;
use Carbon\Carbon;
use Jpeters8889\ApexCharts\Chartable;

abstract class RecipeMetric extends Chartable
{
    public function __construct(protected int $recipeId)
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
        return RecipeMetricModel::query()
            ->where('recipe_id', $this->recipeId)
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
        $lastMetric = RecipeMetricModel::query()
            ->where('recipe_id', $this->recipeId)
            ->latest()
            ->first();

        return $lastMetric ? 'Last updated: ' . $lastMetric->updated_at->format('Y-m-d H:i:s') : null;
    }
}
