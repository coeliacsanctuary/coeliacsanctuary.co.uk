<?php

declare(strict_types=1);

namespace App\Jobs\EatingOut;

use App\Ai\Agents\EateryCountyDescriptionAgent;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

#[Tries(1)]
#[Timeout(300)]
class GenerateCountyDescriptionJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 600;

    public function __construct(protected EateryCounty $county)
    {
        $this->delay(now()->addSeconds(10));
    }

    public function uniqueId(): int
    {
        return $this->county->id;
    }

    public function handle(): void
    {
        if ($this->county->county === 'Nationwide') {
            return;
        }

        $response = EateryCountyDescriptionAgent::make()->prompt($this->buildPrompt());

        $this->county->update(['description' => $response->text]);
    }

    protected function buildPrompt(): string
    {
        $towns = $this->getTowns();

        $eateries = number_format($towns->sum(fn (EateryTown $town) => $town->live_eateries_count + $town->live_branches_count));
        $averageRating = number_format($towns->avg('avg_rating') ?? 0, 1);

        return <<<PROMPT
        County: {$this->county->county}
        Number of Towns: {$towns->count()}
        Number of Eateries: {$eateries}
        Average Eatery Rating: {$averageRating}
        PROMPT;
    }

    /** @return Collection<int, EateryTown> */
    protected function getTowns(): Collection
    {
        return $this->county
            ->activeTowns()
            ->withCount(['liveEateries', 'liveBranches'])
            ->withAvg('reviews as avg_rating', 'rating')
            ->get();
    }
}
