<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\Review;

use App\Actions\EatingOut\CreateEateryReviewAction;
use App\Http\Requests\EatingOut\EateryCreateReviewRequest;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\DetermineNationwideBranchFromNamePipeline;
use Illuminate\Http\RedirectResponse;

class StoreController
{
    public function __invoke(
        EateryCreateReviewRequest $request,
        EateryCounty $county,
        EateryTown $town,
        EateryArea $area,
        Eatery $eatery,
        NationwideBranch $nationwideBranch,
        CreateEateryReviewAction $createEateryReviewAction,
        DetermineNationwideBranchFromNamePipeline $determineNationwideBranchFromNamePipeline,
    ): RedirectResponse {
        /** @var array $requestData */
        $requestData = $request->validated();

        if ($nationwideBranch->exists() && $nationwideBranch->id !== null) {
            abort_if($nationwideBranch->wheretoeat_id !== $eatery->id, RedirectResponse::HTTP_NOT_FOUND);
        }

        $branch = $determineNationwideBranchFromNamePipeline->run(
            $eatery,
            $nationwideBranch,
            $request->string('branch_name')->toString(),
        );

        if ($branch) {
            $requestData['nationwide_branch_id'] = $branch->id;
        }

        $createEateryReviewAction->handle($eatery, [
            ...$requestData,
            'ip' => $request->ip(),
            'approved' => false,
        ]);

        return redirect()->back();
    }
}
