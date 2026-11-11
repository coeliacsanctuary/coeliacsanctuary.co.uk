<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Signature;
use App\Models\MailcoachSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

#[Signature('coeliac:check-for-mailcoach-scheduled-emails')]
class CheckForMailcoachScheduledEmailsCommand extends Command
{
    public function handle(): void
    {
        MailcoachSchedule::query()->where('scheduled_at', '<=', Carbon::now()->addMinute())
            ->get()
            ->each(function (MailcoachSchedule $schedule): void {
                Http::get(config('mailcoach-sdk.endpoint'));

                $schedule->delete();
            });
    }
}
