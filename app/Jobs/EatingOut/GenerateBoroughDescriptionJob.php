<?php

declare(strict_types=1);

namespace App\Jobs\EatingOut;

use App\Ai\Agents\EateryBoroughDescriptionAgent;
use App\Models\EatingOut\EateryTown;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

#[Tries(1)]
#[Timeout(300)]
class GenerateBoroughDescriptionJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 600;

    public function __construct(protected EateryTown $borough)
    {
        $this->delay(now()->addSeconds(10));
    }

    public function uniqueId(): int
    {
        return $this->borough->id;
    }

    public function handle(): void
    {
        if ($this->borough->county->county !== 'London') {
            return;
        }

        $response = EateryBoroughDescriptionAgent::make()->prompt($this->buildPrompt());

        $this->borough->update(['description' => $response->text]);
    }

    protected function buildPrompt(): string
    {
        $this->borough->loadCount(['liveEateries', 'liveBranches']);

        $eateries = number_format($this->borough->live_eateries_count + $this->borough->live_branches_count);
        $averageRating = number_format($this->borough->reviews()->avg('rating') ?? 0, 1);

        return <<<PROMPT
        Borough: {$this->borough->town}
        Number of Areas: {$this->borough->areas()->count()}
        Number of Eateries: {$eateries}
        Average Eatery Rating: {$averageRating}
        PROMPT;
    }
}
