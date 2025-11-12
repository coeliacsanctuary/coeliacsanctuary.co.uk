<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\Report;

use App\Actions\EatingOut\CreateEateryReportAction;
use App\Http\Api\V1\Requests\EatingOut\CreateReportRequest;
use App\Models\EatingOut\Eatery;
use Illuminate\Http\Response;

class StoreController
{
    public function __invoke(Eatery $eatery, CreateReportRequest $request, CreateEateryReportAction $createEateryReportAction): Response
    {
        $createEateryReportAction->handle(
            $eatery,
            $request->string('details')->toString(),
            $request->has('branch_id') ? $request->integer('branch_id') : null,
            $request->has('branch_name') ? $request->string('branch_name')->toString() : null,
        );

        return response(status: Response::HTTP_CREATED);
    }
}
