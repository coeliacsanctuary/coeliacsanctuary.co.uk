<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\RecommendAPlace;

use App\Actions\EatingOut\CreatePlaceRecommendationAction;
use App\Http\Api\V1\Requests\EatingOut\RecommendAPlaceRequest;
use Illuminate\Http\Response;

class StoreController
{
    public function __invoke(RecommendAPlaceRequest $request, CreatePlaceRecommendationAction $createPlaceRecommendationAction): Response
    {
        /** @var array $data */
        $data = $request->validated();

        $createPlaceRecommendationAction->handle($data);

        return response(status: Response::HTTP_CREATED);
    }
}
