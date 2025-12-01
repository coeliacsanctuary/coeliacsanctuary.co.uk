<?php

declare(strict_types=1);

namespace App\Actions\Journey;

use App\DataObjects\Journey\QueuedPageViewData;
use App\Jobs\Journey\LogPageViewJob;

class QueuePageViewAction
{
    public function handle(string $sessionId, string $path, ?string $route = null): QueuedPageViewData
    {
        $data = new QueuedPageViewData(
            $sessionId,
            $path,
            $route,
            now()->getTimestamp(),
        );

        LogPageViewJob::dispatch($data);

        return $data;
    }
}
