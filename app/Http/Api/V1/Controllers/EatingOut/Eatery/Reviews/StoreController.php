<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews;

use App\Actions\EatingOut\CreateEateryReviewAction;
use App\Http\Api\V1\Requests\EatingOut\CreateReviewRequest;
use App\Models\EatingOut\Eatery;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\DetermineNationwideBranchFromNamePipeline;
use Illuminate\Http\Response;

class StoreController
{
    public function __invoke(
        CreateReviewRequest $request,
        Eatery $eatery,
        DetermineNationwideBranchFromNamePipeline $determineNationwideBranchFromNamePipeline,
        CreateEateryReviewAction $createEateryReviewAction,
    ): Response {
        $requestData = $request->validated();
        unset($requestData['branch_id']);

        $branch = null;

        if ($request->filled('branch_id')) {
            $branch = $eatery->nationwideBranches()->where('id', $request->integer('branch_id'))->first();

            abort_if( ! $branch, 404);
        }

        if ($request->isNationwide() && ! $branch) {
            $branch = $determineNationwideBranchFromNamePipeline->run(
                $eatery,
                null,
                $request->string('branch_name')->toString(),
            );
        }

        if ($branch) {
            $requestData['nationwide_branch_id'] = $branch->id;
        }

        $createEateryReviewAction->handle($eatery, [
            ...$requestData,
            'ip' => $request->ip(),
            'approved' => $request->boolean('admin_review'),
        ]);

        return response(status: Response::HTTP_CREATED);
    }
}
