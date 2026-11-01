<?php

declare(strict_types=1);

namespace App\Jobs\EatingOut;

use Illuminate\Foundation\Queue\Queueable;
use App\Actions\EatingOut\CheckEateryWebsiteAction;
use App\Actions\EatingOut\CreateEateryAlertAction;
use App\DataObjects\EatingOut\EateryWebsiteCheckResult;
use App\Models\EatingOut\Eatery;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckSingleEateryWebsiteJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Eatery $eatery)
    {
        //
    }

    public function handle(): void
    {
        $result = app(CheckEateryWebsiteAction::class)->handle($this->eatery);

        if ( ! $result->success) {
            $this->handleFailure($result);
        }

        $this->eatery->check()->updateOrCreate(
            ['wheretoeat_id' => $this->eatery->id],
            ['website_checked_at' => now()],
        );
    }

    protected function handleFailure(EateryWebsiteCheckResult $result): void
    {
        $message = match(true) {
            $result->timedOut => "Website connection timed out",
            default => "Website returned a {$result->statusCode} status code",
        };

        app(CreateEateryAlertAction::class)->handle($this->eatery, 'website', $message);
    }
}
