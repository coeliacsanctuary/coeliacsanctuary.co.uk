<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Http\Request;

/** @mixin PendingEatery */
class EateryTripResource extends EateryBrowseResource
{
    public function __construct(PendingEatery $resource, protected Eatery | NationwideBranch | null $model = null)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var float | string | null $averageRating */
        $averageRating = $this->model?->getAttribute('reviews_avg_rating');

        $chain = $this->model instanceof NationwideBranch ? $this->model->eatery->name : null;

        return [
            ...parent::toArray($request),
            'branchId' => $this->branchId,
            'name' => $this->model?->name ?: $chain,
            'town' => $this->model?->town?->town,
            'chain' => $this->model?->name ? $chain : null,
            'rating' => $averageRating !== null ? round((float) $averageRating, 1) : null,
            'reviewsCount' => (int) $this->model?->getAttribute('reviews_count'),
            'distanceAlongRoute' => round((float) $this->ordering),
            'distanceFromRoute' => round((float) $this->distance),
            'durationAlongRoute' => round((float) $this->durationAlongRoute),
        ];
    }
}
