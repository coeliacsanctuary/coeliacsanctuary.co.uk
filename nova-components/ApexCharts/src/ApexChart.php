<?php

declare(strict_types=1);

namespace Jpeters8889\ApexCharts;

use Laravel\Nova\Card;

class ApexChart extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/3';

    protected string $chartable;

    protected bool $customDateRange = false;

    protected array $params = [];

    public function __construct(?string $chartable = null)
    {
        if ($chartable) {
            $this->chartable = $chartable;
        }

        parent::__construct();
    }

    public function component()
    {
        return 'apex-charts';
    }

    /** @param class-string<Chartable> $chartable */
    public function loadChartable(string $chartable): self
    {
        $this->chartable = $chartable;

        return $this;
    }

    public function fullWidth(): self
    {
        return $this->width(self::FULL_WIDTH);
    }

    public function withCustomDateRange(): self
    {
        $this->customDateRange = true;

        return $this;
    }

    public function withParams(array $params)
    {
        $this->params = array_merge($this->params, $params);

        $this->meta['params'] = array_merge($this->meta['params'] ?? [], $params);

        return $this;
    }

    public function jsonSerialize(): array
    {
        $chartable = app($this->chartable, $this->params);

        return array_merge([
            'chartable' => $this->chartable,
            'name' => $chartable->name(),
            'customDateRange' => $this->customDateRange,
        ], parent::jsonSerialize());
    }
}
