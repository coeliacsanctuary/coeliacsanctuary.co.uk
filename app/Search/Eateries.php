<?php

declare(strict_types=1);

namespace App\Search;

use Algolia\ScoutExtended\Builder;
use Algolia\ScoutExtended\Searchable\Aggregator;
use App\Contracts\Search\IsSearchable;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;

class Eateries extends Aggregator implements IsSearchable
{
    /** @var Eatery|NationwideBranch */
    protected $model;

    /**
     * @var string[]
     */
    protected $models = [
        Eatery::class,
        NationwideBranch::class,
    ];

    public function shouldBeSearchable()
    {
        return $this->model->shouldBeSearchable();
    }

    public static function search($query = '', $callback = null): Builder
    {
        if (app()->runningUnitTests()) {
            /** @var Builder $search */
            $search = Eatery::search($query, $callback);

            return $search;
        }

        /** @var Builder $search */
        $search = parent::search($query, $callback);

        return $search;
    }
}
