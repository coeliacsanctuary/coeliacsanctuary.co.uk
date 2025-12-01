<?php

declare(strict_types=1);

namespace App\Jobs\Journey;

use App\DataObjects\Journey\QueuedPageViewData;
use App\Models\Journeys\Journey;
use App\Models\Journeys\Page;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class LogPageViewJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(protected QueuedPageViewData $data)
    {
        //
    }

    public function handle(): void
    {
        try {
            $request = Http::journeyTracker()->post('/api/page-view', $this->data->toArray());

            dd($request, $request->json());
        } catch (Throwable $e) {
            dd($e);
        }
    }
}
