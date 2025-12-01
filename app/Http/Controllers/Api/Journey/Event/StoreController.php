<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Journey\Event;

use App\Http\Requests\Api\Journey\Event\StoreRequest;
use App\Jobs\Journey\LogPageEventJob;
use Illuminate\Http\Response;

class StoreController
{
    public function __invoke(StoreRequest $request): Response
    {
        LogPageEventJob::dispatch($request->toData());

        return response()->noContent();
    }
}
