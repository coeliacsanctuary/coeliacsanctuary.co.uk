<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\MailcoachSchedule;

use App\Http\Requests\Api\MailcoachSchedule\StoreRequest;
use App\Models\MailcoachSchedule;
use Illuminate\Http\Response;

class StoreController
{
    public function __invoke(StoreRequest $request): Response
    {
        MailcoachSchedule::query()->create([
            'scheduled_at' => $request->date('time'),
        ]);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
