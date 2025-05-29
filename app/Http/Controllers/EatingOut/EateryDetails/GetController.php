<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\EateryDetails;

use App\Actions\EatingOut\ComputeEateryBackLinkAction;
use App\Actions\EatingOut\LoadCompleteEateryDetailsForRequestAction;
use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Http\Response\Inertia;
use App\Models\EatingOut\Eatery;
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
        Eatery $eatery,
        NationwideBranch $nationwideBranch,
        Request $request,
        Inertia $inertia,
        GetEatingOutOpenGraphImageAction $getOpenGraphImageAction,
        LoadCompleteEateryDetailsForRequestAction $loadCompleteEateryDetailsForRequestAction,
        ComputeEateryBackLinkAction $computeEateryBackLinkAction,
    ): Response {
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
            $request->boolean('show-all-reviews') !== true
        );

        [$name, $previous] = $computeEateryBackLinkAction->handle($eatery);

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
