<?php

declare(strict_types=1);

namespace App\Jobs\Journey;

use App\DataObjects\Journey\QueuedEventData;
use App\Models\Journeys\Event;
use App\Models\Journeys\Journey;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class LogPageEventJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(protected QueuedEventData $data)
    {
        //
    }

    public function handle(): void
    {
        try {
            Http::journeyTracker()->post('/api/event', $this->data->toArray());
        } catch (Throwable $e) {
            dd($e);
        }
    }
}
