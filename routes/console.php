<?php

declare(strict_types=1);

use App\Console\Commands\ApplyMassDiscountsCommand;
use App\Console\Commands\CheckForMailcoachScheduledEmailsCommand;
use App\Console\Commands\CleanUpNovaPreviewsCommand;
use App\Console\Commands\CloseBasketsCommand;
use App\Console\Commands\ClosePendingOrdersCommand;
use App\Console\Commands\PrepareShopReviewInvitationsCommand;
use App\Console\Commands\ProcessEateryWebsiteChecksCommand;
use App\Console\Commands\PublishItemsCommand;
use App\Console\Commands\RemoveCollectionsFromHomepageCommand;
use App\Console\Commands\SendAbandonedBasketEmailCommand;
use App\Console\Commands\SummariseAskSealiacChatsCommand;
use App\Jobs\Metrics\Orchestrators\BaseOrchestratorJob;
use App\Models\EatingOut\EateryTrip;
use Illuminate\Support\Facades\Schedule;

Schedule::command(CloseBasketsCommand::class)->everyMinute();
Schedule::command(ClosePendingOrdersCommand::class)->everyMinute();
Schedule::command(SendAbandonedBasketEmailCommand::class)->everyMinute();
Schedule::command(ApplyMassDiscountsCommand::class)->everyMinute();
Schedule::command(PrepareShopReviewInvitationsCommand::class)->everyMinute();
Schedule::command(PublishItemsCommand::class)->everyMinute();
Schedule::command(RemoveCollectionsFromHomepageCommand::class)->everyMinute();
Schedule::command(CheckForMailcoachScheduledEmailsCommand::class)->everyMinute();
Schedule::command(SummariseAskSealiacChatsCommand::class)->everyMinute();
Schedule::command(ProcessEateryWebsiteChecksCommand::class)->daily();
Schedule::command(CleanUpNovaPreviewsCommand::class)->daily();
Schedule::command('model:prune', ['--model' => [EateryTrip::class]])->daily();

BaseOrchestratorJob::scheduleAll(Schedule::getFacadeRoot());

if (app()->environment('production')) {
    Schedule::command('about')->thenPing('http://beats.envoyer.io/heartbeat/oKkQ7etgPUsSnOW');

    Schedule::command('backup:run')->hourly();
    Schedule::command('backup:clean')->daily()->at('02:00');
}
