<?php

declare(strict_types=1);

namespace App\Jobs\EatingOut;

use App\Ai\Agents\EateryTownDescriptionAgent;
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
class GenerateTownDescriptionJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 600;

    public function __construct(protected EateryTown $town)
    {
        $this->delay(now()->addSeconds(10));
    }

    public function uniqueId(): int
    {
        return $this->town->id;
    }

    public function handle(): void
    {
        if ($this->town->county?->country?->country === 'Nationwide') {
            return;
        }

        if ($this->town->county?->county === 'London') {
            return;
        }

        $response = EateryTownDescriptionAgent::make()->prompt($this->buildPrompt());

        $this->town->update(['description' => $response->text]);
    }

    protected function buildPrompt(): string
    {
        $this->town->loadCount(['liveEateries', 'liveBranches']);

        $eateries = number_format($this->town->live_eateries_count + $this->town->live_branches_count);
        $averageRating = number_format((float) $this->town->reviews()->avg('rating'), 1);

        return <<<PROMPT
        Town: {$this->town->town}
        County: {$this->town->county?->county}
        Number of Eateries: {$eateries}
        Average Eatery Rating: {$averageRating}
        PROMPT;
    }
}
