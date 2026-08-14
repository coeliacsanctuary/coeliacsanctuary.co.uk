<?php

declare(strict_types=1);

namespace App\Contracts\Metrics;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

interface MetricSource
{
    /** @return Builder<Model> */
    public function query(): Builder;

    public function metricsRelation(): string;

    public function dispatch(Model $model, int $delaySeconds, Carbon $date): void;
}
