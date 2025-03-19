<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\EateryDetails;

use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Http\Response\Inertia;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Resources\EatingOut\EateryDetailsResource;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class GetController
{
    public function __invoke(
        EateryCounty $county,
        EateryTown $town,
        Eatery $eatery,
        NationwideBranch $nationwideBranch,
        Request $request,
        Inertia $inertia,
        GetEatingOutOpenGraphImageAction $getOpenGraphImageAction,
    ): Response {
        if ($request->routeIs('eating-out.nationwide.show', 'eating-out.nationwide.show.branch')) {
            /** @var EateryCounty $county */
            $county = EateryCounty::query()->firstWhere('county', 'Nationwide');

            /** @var EateryTown $town */
            $town = EateryTown::query()->firstWhere('town', 'nationwide');
        }

        $county->load(['country']);
        $town->setRelation('county', $county);

        $eatery->setRelation('town', $town);
        $eatery->setRelation('county', $county);

        $eatery->load([
            'adminReview', 'adminReview.images', 'reviewImages', 'reviews.images', 'restaurants', 'features', 'openingTimes',
            'reviews' => function (HasMany $builder) {
                /** @var HasMany<EateryReview, Eatery> $builder */
                return $builder->latest()->where('admin_review', false);
            },
        ]);

        if ($request->routeIs('eating-out.nationwide.show')) {
            $eatery->load(['nationwideBranches.eatery', 'nationwideBranches.town', 'nationwideBranches.town.county', 'nationwideBranches.county', 'nationwideBranches.country']);
        }

        if ($request->routeIs('eating-out.nationwide.show.branch')) {
            abort_if($nationwideBranch->eatery->isNot($eatery), Response::HTTP_NOT_FOUND);

            $nationwideBranch->load(['county', 'town']);

            $eatery->setRelation('branch', $nationwideBranch);
        }

        $previous = URL::previous(route('eating-out.town', ['county' => $eatery->county, 'town' => $eatery->town]));

        /** @var \Illuminate\Routing\Route | null $route */
        $route = Route::getRoutes()->match(Request::create($previous));

        $name = match ($route?->getName()) {
            'eating-out.town' => "Back to {$eatery->town?->town}",
            'eating-out.browse', 'eating-out.browse.any' => 'Back to map',
            'eating-out.search.show', 'search.index' => 'Back to search results',
            'eating-out.index' => 'Back to eating out guide',
            default => "Back to {$eatery->county?->county}"
        };

        return $inertia
            ->title("Gluten free at {$eatery->full_name}")
            ->metaDescription("Eat gluten free at {$eatery->full_name}")
            ->metaTags($eatery->keywords())
            ->metaImage($getOpenGraphImageAction->handle($eatery))
            ->render('EatingOut/Details', [
                'eatery' => fn () => new EateryDetailsResource($eatery),
                'previous' => $previous,
                'name' => $name,
            ])
            ->toResponse($request)
            ->setStatusCode($eatery->closed_down ? Response::HTTP_GONE : Response::HTTP_OK);
    }
}
