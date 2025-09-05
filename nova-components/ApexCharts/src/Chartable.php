<?php

declare(strict_types=1);

namespace Jpeters8889\ApexCharts;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Jpeters8889\ApexCharts\DTO\DateRange;

abstract class Chartable
{
    public const string BAR_CHART = 'bar';

    public const string LINE_CHART = 'line';

    public const string AREA_CHART = 'area';

    public const string DATE_RANGE_TODAY = 'today';

    public const string DATE_RANGE_YESTERDAY = 'yesterday';

    public const string DATE_RANGE_THIS_WEEK = 'thisWeek';

    public const string DATE_RANGE_THIS_MONTH = 'thisMonth';

    public const string DATE_RANGE_THIS_YEAR = 'thisYear';

    public const string DATE_RANGE_PAST_WEEK = 'last7';

    public const string DATE_RANGE_PAST_2_WEEKS = 'last14';

    public const string DATE_RANGE_PAST_MONTH = 'lastMonth';

    public const string DATE_RANGE_PAST_YEAR = 'lastYear';

    abstract protected function getData(Carbon $startDate, Carbon $endDate): int|float|array;

    protected function type(): string
    {
        return self::BAR_CHART;
    }

    public function name(): string
    {
        return Str::of(class_basename(static::class))->headline()->toString();
    }

    public function defaultDateRange(): string
    {
        return self::DATE_RANGE_PAST_WEEK;
    }

    public function dateRanges(): array
    {
        return [
            self::DATE_RANGE_TODAY => new DateRange(
                label: 'Today',
                startDate: Carbon::now()->startOfDay(),
                endDate: Carbon::now()->endOfDay(),
                unit: 'hour',
                dateFormat: 'ga'
            ),
            self::DATE_RANGE_YESTERDAY => new DateRange(
                label: 'Yesterday',
                startDate: Carbon::yesterday()->startOfDay(),
                endDate: Carbon::yesterday()->endOfDay(),
                unit: 'hour',
                dateFormat: 'ga'
            ),
            self::DATE_RANGE_THIS_WEEK => new DateRange(
                label: 'So far this week',
                startDate: Carbon::now()->startOf('week'),
                endDate: Carbon::now()->endOfDay(),
                unit: 'day',
                dateFormat: 'd/m'
            ),
            self::DATE_RANGE_THIS_MONTH => new DateRange(
                label: 'So far this month',
                startDate: Carbon::now()->startOf('month'),
                endDate: Carbon::now()->endOfDay(),
                unit: 'day',
                dateFormat: 'd/m'
            ),
            self::DATE_RANGE_THIS_YEAR => new DateRange(
                label: 'So far this year',
                startDate: Carbon::now()->startOf('year'),
                endDate: Carbon::now()->endOfDay(),
                unit: 'month',
                dateFormat: 'M'
            ),
            self::DATE_RANGE_PAST_WEEK => new DateRange(
                label: 'Past Week',
                startDate: Carbon::now()->subDays(7)->startOfDay(),
                endDate: Carbon::now()->endOfDay(),
                unit: 'day',
                dateFormat: 'd/m'
            ),
            self::DATE_RANGE_PAST_2_WEEKS => new DateRange(
                label: 'Past 2 Weeks',
                startDate: Carbon::now()->subDays(14)->startOfDay(),
                endDate: Carbon::now()->endOfDay(),
                unit: 'day',
                dateFormat: 'd/m'
            ),
            self::DATE_RANGE_PAST_MONTH => new DateRange(
                label: 'Past Month',
                startDate: Carbon::now()->subMonth()->startOfDay(),
                endDate: Carbon::now()->endOfDay(),
                unit: 'day',
                dateFormat: 'd/m'
            ),
            self::DATE_RANGE_PAST_YEAR => new DateRange(
                label: 'Past Year',
                startDate: Carbon::now()->subYear()->startOfMonth(),
                endDate: Carbon::now()->endOfMonth(),
                unit: 'month',
                dateFormat: 'M y'
            ),
        ];
    }

    final public function render(DateRange $dateRange): array
    {
        return [
            'type' => $this->type(),
            'data' => $this->data($dateRange),
            'options' => $this->chartOptions($dateRange),
        ];
    }

    final protected function defaultOptions(DateRange $dateRange): array
    {
        return [
            'chart' => [
                'selection' => [
                    'enabled' => false,
                ],
                'toolbar' => [
                    'show' => false,
                ],
                'zoom' => [
                    'enabled' => false,
                ],
            ],
            'markers' => [
                'size' => 6,
            ],
            'stroke' => [
                'size' => 2,
            ],
            'xaxis' => [
                'categories' => $this->getLabels($dateRange),
            ],
        ];
    }

    final protected function chartOptions(DateRange $dateRange): array
    {
        return array_merge_recursive($this->defaultOptions($dateRange), $this->options());
    }

    protected function options(): array
    {
        return [];
    }

    protected function getLabels(DateRange $dateRange): array
    {
        $labels = [];

        $start = $dateRange->startDate->clone();

        while ($start->lessThan($dateRange->endDate)) {
            $labels[] = $start->format($dateRange->dateFormat);
            $start->add(1, $dateRange->unit);
        }

        return $labels;
    }

    protected function data(DateRange $dateRange): array
    {
        return [[
            'name' => $this->name(),
            'data' => $this->calculateData($dateRange),
            'color' => '#80CCFC',
        ]];
    }

    protected function calculateData(DateRange $dateRange): array
    {
        $unit = $dateRange->unit;

        $data = [];

        $start = $dateRange->startDate->clone();

        while ($start->lessThan($dateRange->endDate)) {
            $data[] = $this->getData($start->startOf($unit), $start->clone()->endOf($unit));

            $start->add(1, $unit);
        }

        return $data;
    }
}
