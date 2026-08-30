<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs\Widgets;

use App\Models\Blogs\Blog;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BlogMetricChart extends ChartWidget
{
    public ?Model $record = null;

    public ?string $filter = 'last14';

    protected ?string $maxHeight = '200px';

    abstract protected function column(): string;

    protected function getType(): string
    {
        return 'line';
    }

    /** @return array<string, string> */
    protected function getFilters(): ?array
    {
        return [
            'last7' => 'Past Week',
            'last14' => 'Past 2 Weeks',
            'lastMonth' => 'Past Month',
            'lastYear' => 'Past Year',
        ];
    }

    public function getDescription(): ?string
    {
        $latestMetric = $this->blog()->metrics()->latest()->first();

        if ( ! $latestMetric) {
            return null;
        }

        return 'Last updated: ' . $latestMetric->updated_at->format('Y-m-d H:i:s');
    }

    /** @return array<string, mixed> */
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'min' => 0,
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    /** @return array{datasets: array<int, array{label: string, data: array<int, int>}>, labels: array<int, string>} */
    protected function getData(): array
    {
        ['labels' => $labels, 'data' => $data] = $this->filter === 'lastYear'
            ? $this->monthlyMetrics()
            : $this->dailyMetrics();

        return [
            'datasets' => [
                [
                    'label' => $this->heading ?? '',
                    'data' => $data,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /** @return array{labels: array<int, string>, data: array<int, int>} */
    protected function dailyMetrics(): array
    {
        $start = today()->subDays($this->days());
        $end = today();

        $metrics = $this->blog()
            ->metrics()
            ->whereBetween('date', [$start, $end])
            ->pluck($this->column(), 'date');

        $labels = [];
        $data = [];

        for ($date = $start->copy(); $date->lessThanOrEqualTo($end); $date->addDay()) {
            $labels[] = $date->format('d/m');
            $data[] = (int) ($metrics[$date->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /** @return array{labels: array<int, string>, data: array<int, int>} */
    protected function monthlyMetrics(): array
    {
        $start = today()->subYear()->startOfMonth();
        $end = today()->endOfMonth();

        $metrics = $this->blog()
            ->metrics()
            ->whereBetween('date', [$start, $end])
            ->groupBy('month')
            ->select([
                DB::raw("DATE_FORMAT(`date`, '%Y-%m') as month"),
                DB::raw("SUM(`{$this->column()}`) as total"),
            ])
            ->pluck('total', 'month');

        $labels = [];
        $data = [];

        for ($month = $start->copy(); $month->lessThanOrEqualTo($end); $month->addMonth()) {
            $labels[] = $month->format('M y');
            $data[] = (int) ($metrics[$month->format('Y-m')] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    protected function days(): int
    {
        return match ($this->filter) {
            'last7' => 7,
            'lastMonth' => 30,
            default => 14,
        };
    }

    protected function blog(): Blog
    {
        assert($this->record instanceof Blog);

        return $this->record;
    }
}
