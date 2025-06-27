<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\ReportEatery;

use App\Actions\EatingOut\CreateEateryReportAction;
use App\Http\Requests\EatingOut\EateryCreateReportRequest;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\DetermineNationwideBranchFromName\DetermineNationwideBranchFromNamePipeline;
use Illuminate\Http\RedirectResponse;

class StoreController
{
    public function __invoke(
        EateryCreateReportRequest $request,
        EateryCounty $county,
        EateryTown $town,
        EateryArea $area,
        Eatery $eatery,
        CreateEateryReportAction $createEateryReportAction,
        DetermineNationwideBranchFromNamePipeline $determineNationwideBranchFromNamePipeline,
    ): RedirectResponse {
        if ($request->string('branch_name')->isNotEmpty()) {
            $branch = $determineNationwideBranchFromNamePipeline->run(
                $eatery,
                null,
                $request->string('branch_name')->toString(),
            );

            if ($branch) {
                $request->merge(['branch_id' => $branch->id]);
            }
        }

        $createEateryReportAction->handle(
            $eatery,
            $request->string('details')->toString(),
            $request->has('branch_id') ? $request->integer('branch_id') : null,
            $request->has('branch_name') ? $request->string('branch_name')->toString() : null,
        );

        return redirect()->back();
    }
}
