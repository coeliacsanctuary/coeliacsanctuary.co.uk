<?php

declare(strict_types=1);

namespace App\DataObjects\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EaterySearchTerm;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Resources\EatingOut\EateryListResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class GetEateriesPipelineData
{
    /**
     * @param  array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null, county: string | int | null }  $filters
     * @param  null | Collection<int, PendingEatery>  $eateries
     * @param  null | LengthAwarePaginator<int, PendingEatery>  $paginator
     * @param  null | Collection<int, Eatery>  $hydrated
     * @param  null | Collection<int, NationwideBranch>  $hydratedBranches
     * @param  class-string<JsonResource>  $jsonResource,
     * @param  null | LengthAwarePaginator<int, JsonResource> | Collection<int, JsonResource>  $serialisedEateries
     */
    public function __construct(
        public array $filters,
        public ?EateryTown $town = null,
        public ?EateryArea $area = null,
        public ?EaterySearchTerm $searchTerm = null,
        public ?LatLng $latLng = null,
        public ?Collection $eateries = null,
        public ?LengthAwarePaginator $paginator = null,
        public ?Collection $hydrated = null,
        public ?Collection $hydratedBranches = null,
        public string $jsonResource = EateryListResource::class,
        public LengthAwarePaginator|Collection|null $serialisedEateries = null,
        public bool $throwSearchException = true,
    ) {}
}
