<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\EateryDetails;

use App\Actions\EatingOut\ComputeEateryBackLinkAction;
use App\Actions\EatingOut\GetNearbyEateriesAction;
use App\Actions\EatingOut\LoadCompleteEateryDetailsForRequestAction;
use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Resources\EatingOut\EateryDetailsResource;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GetController
{
    public function __invoke(
        EateryCounty $county,
        EateryTown $town,
        EateryArea $area,
        Eatery $eatery,
        NationwideBranch $nationwideBranch,
        Request $request,
        Inertia $inertia,
        GetEatingOutOpenGraphImageAction $getOpenGraphImageAction,
        LoadCompleteEateryDetailsForRequestAction $loadCompleteEateryDetailsForRequestAction,
        ComputeEateryBackLinkAction $computeEateryBackLinkAction,
        GetNearbyEateriesAction $getNearbyEateriesAction,
    ): Response {
        if ($area->exists) {
            /** @var EateryCounty $county */
            $county = EateryCounty::query()->where('slug', 'london')->first();

            $eatery->setRelation('county', $county);
        }

        $pageType = match ($request->route()?->getName()) {
            'eating-out.nationwide.show' => 'nationwide',
            'eating-out.nationwide.show.branch' => 'branch',
            default => 'eatery',
        };

        $loadCompleteEateryDetailsForRequestAction->handle(
            $eatery,
            $county,
            $town,
            $nationwideBranch,
            $pageType,
            $request->boolean('show-all-reviews'),
        );

        [$name, $previous] = $computeEateryBackLinkAction->handle($eatery);

        return $inertia
            ->title("Gluten free at {$eatery->full_name}")
            ->metaDescription("Eat gluten free at {$eatery->full_name}")
            ->metaTags($eatery->keywords())
            ->metaImage($getOpenGraphImageAction->handle($eatery))
            ->schema($eatery->schema()->toScript())
            ->breadcrumbs(collect(array_filter([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Eating Out', route('eating-out.index')),
                $county->exists ? new BreadcrumbItemData($county->county, route('eating-out.county', $county)) : null,
                $county->exists ? new BreadcrumbItemData($town->town, route('eating-out.town', ['county' => $county, 'town' => $town])) : null,
                $area->exists ? new BreadcrumbItemData($area->area, route('eating-out.london.borough.area', ['borough' => $town, 'area' => $area])) : null,
                new BreadcrumbItemData($eatery->full_name),
            ])))
            ->render('EatingOut/Details', [
                'eatery' => fn () => new EateryDetailsResource($eatery),
                'previous' => $previous,
                'name' => $name,
                'nearbyEateries' => Inertia::defer(fn () => $getNearbyEateriesAction->handle($nationwideBranch->exists ? $nationwideBranch : $eatery)),
            ])
            ->toResponse($request)
            ->setStatusCode($eatery->closed_down ? Response::HTTP_GONE : Response::HTTP_OK);
    }
}
