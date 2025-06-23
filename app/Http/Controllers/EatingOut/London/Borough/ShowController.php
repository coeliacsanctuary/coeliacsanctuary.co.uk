<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\London\Borough;

use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Http\Response\Inertia;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Resources\EatingOut\LondonBoroughPageResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Inertia\Response;

class ShowController
{
    public function __invoke(EateryTown $borough, Inertia $inertia, GetEatingOutOpenGraphImageAction $getOpenGraphImageAction): Response
    {
        /** @var EateryCounty $county */
        $county = EateryCounty::query()->where('slug', 'london')->first();

        if ($borough->county_id !== $county->id) {
            abort(404);
        }

        $borough
            /** @phpstan-ignore-next-line  */
            ->load(['areas' => fn (Relation $relation) => $relation->chaperone()->with(['liveEateries', 'liveBranches'])->orderBy('area')])
            ->setRelation('county', $county);

        return $inertia
            ->title("Gluten Free Places to Eat in the London borough of {$borough->town}")
            ->metaDescription("Coeliac Sanctuary gluten free places in the London borough of {$borough->town} | Places can cater to Coeliac and Gluten Free diets in {$borough->town}, {$county->county}!")
            ->metaTags($borough->keywords())
            ->metaImage($getOpenGraphImageAction->handle($borough))
            ->render('EatingOut/LondonBorough', [
                'borough' => fn () => new LondonBoroughPageResource($borough),
            ]);
    }
}
