<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\RecommendAPlace\Check;

use App\DataObjects\EatingOut\RecommendAPlaceExistsCheckData;
use App\Pipelines\EatingOut\CheckRecommendedPlace\CheckRecommendedPlacePipeline;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StoreController
{
    public function __invoke(Request $request, CheckRecommendedPlacePipeline $checkedRecommendedPlacePipeline): array|Response
    {
        $data = new RecommendAPlaceExistsCheckData(
            name: $request->has('placeName') ? $request->string('placeName')->toString() : null,
            location: $request->has('placeLocation') ? $request->string('placeLocation')->toString() : null,
        );

        $result = $checkedRecommendedPlacePipeline->run($data);

        if ($result->id !== null) {
            return [
                'data' => [
                    'result' => $result->reason,
                    'id' => $result->id,
                    'branchId' => $result->branchId,
                    'label' => $result->label,
                ],
            ];
        }

        return response()->noContent();
    }
}
