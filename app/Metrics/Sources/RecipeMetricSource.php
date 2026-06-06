<?php

declare(strict_types=1);

namespace App\Metrics\Sources;

use App\Contracts\Metrics\MetricSource;
use App\Jobs\Metrics\Recipes\GetRecipeMetricsJob;
use App\Models\Recipes\Recipe;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class RecipeMetricSource implements MetricSource
{
    public function __construct(
        protected ?Carbon $createdAfter = null,
        protected ?Carbon $createdBefore = null,
    ) {
    }

    /** @return Builder<Model> */
    public function query(): Builder
    {
        $query = Recipe::query()->latest();

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

    public function dispatch(Model $model, int $delaySeconds, Carbon $date): void
    {
        assert($model instanceof Recipe);

        GetRecipeMetricsJob::dispatch($model, $date)->delay($delaySeconds);
    }
}
