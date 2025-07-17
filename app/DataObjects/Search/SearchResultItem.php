<?php

declare(strict_types=1);

namespace App\DataObjects\Search;

use App\Contracts\Search\IsSearchable;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Support\Arr;

class SearchResultItem
{
    /**
     * @param  class-string<IsSearchable>  $model
     */
    public function __construct(
        public readonly int $id,
        public readonly string $model,
        public readonly int $score,
        public readonly int $firstWordPosition,
        public readonly ?int $distance = null,
    ) {
        //
    }

    public static function fromSearchableResult(IsSearchable $searchable): self
    {
        /** @var int $score */
        $score = Arr::get($searchable->scoutMetadata(), '_rankingInfo.userScore', 0);

        /** @var int $firstWordPosition */
        $firstWordPosition = Arr::get($searchable->scoutMetadata(), '_rankingInfo.firstMatchedWord', 0);

        /** @var int $exactWords */
        $exactWords = Arr::get($searchable->scoutMetadata(), '_rankingInfo.nbExactWords', 0);

        if ($searchable instanceof Eatery || $searchable instanceof NationwideBranch) {
            $firstWordPosition = $exactWords > 0 ? $firstWordPosition : 999;
        }

        /** @var int $id */
        $id = $searchable->getKey();

        /** @var int|null $distance */
        $distance = $searchable->hasAttribute('_resDistance') ? $searchable->getAttribute('_resDistance') : null;

        return new self(
            $id,
            $searchable::class,
            $score,
            $firstWordPosition,
            $distance,
        );
    }
}
