<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\SuggestEdits;

use App\Actions\EatingOut\StoreSuggestedEditAction;
use App\Http\Api\V1\Requests\EatingOut\SuggestEditRequest;
use App\Models\EatingOut\Eatery;
use Illuminate\Http\Response;

class StoreController
{
    public function __invoke(Eatery $eatery, SuggestEditRequest $request, StoreSuggestedEditAction $storeSuggestedEditAction): Response
    {
        $storeSuggestedEditAction->handle(
            $eatery,
            $request->string('field')->toString(),
            $request->input('value'),
        );

        return response()->noContent();
    }
}
