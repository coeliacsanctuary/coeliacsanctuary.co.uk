<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\Reports;

use App\Actions\EatingOut\CreateEateryReportAction;
use App\Http\Requests\EatingOut\EateryCreateReportRequest;
use App\Models\EatingOut\Eatery;

class StoreController
{
    public function __invoke(EateryCreateReportRequest $request, CreateEateryReportAction $createEateryReportAction, Eatery $eatery): void
    {
        $createEateryReportAction->handle(
            $eatery,
            $request->string('details')->toString(),
            $request->has('branch_id') ? $request->integer('branch_id') : null,
            $request->has('branch_name') ? $request->string('branch_name')->toString() : null,
        );
    }
}
