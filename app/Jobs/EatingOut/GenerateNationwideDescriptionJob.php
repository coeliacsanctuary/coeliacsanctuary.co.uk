<?php

declare(strict_types=1);

namespace App\Jobs\EatingOut;

use App\Ai\Agents\EateryNationwideDescriptionAgent;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection as SupportCollection;

#[Tries(1)]
#[Timeout(300)]
class GenerateNationwideDescriptionJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 600;

    /**
     * A chain needs at least this many reviews before its rating is worth
     * mentioning, otherwise a single five star review reads as top rated.
     */
    protected int $minimumReviewsForRating = 5;

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
        if ($this->county->country?->country !== 'Nationwide') {
            return;
        }

        $response = EateryNationwideDescriptionAgent::make()->prompt($this->buildPrompt());

        $this->county->update(['description' => $response->text]);
    }

    protected function buildPrompt(): string
    {
        $chains = $this->getChains();

        return view('prompts.nationwide-description-prompt', [
            'chains' => $chains,
            'chainsWithBranches' => $chains->filter(fn (Eatery $chain) => $chain->getAttribute('nationwide_branches_count') > 0),
            'branches' => $chains->sum(fn (Eatery $chain) => $chain->getAttribute('nationwide_branches_count')),
            'venueTypes' => $this->summarise($chains->map(fn (Eatery $chain) => $chain->venueType?->venue_type)),
            'cuisines' => $this->summarise($chains->map(fn (Eatery $chain) => $chain->cuisine?->cuisine)),
            'minimumReviewsForRating' => $this->minimumReviewsForRating,
        ])->render();
    }

    /** @param  SupportCollection<int, string | null>  $values */
    protected function summarise(SupportCollection $values): string
    {
        return $values
            ->filter()
            ->countBy()
            ->sortDesc()
            ->map(fn (int $count, string $value) => "{$value} ({$count})")
            ->implode(', ');
    }

    /** @return Collection<int, Eatery> */
    protected function getChains(): Collection
    {
        return $this->county
            ->eateries()
            ->where('live', true)
            ->where('closed_down', false)
            ->withCount(['reviews', 'nationwideBranches'])
            ->withAvg('reviews', 'rating')
            ->with(['venueType', 'cuisine'])
            ->orderBy('nationwide_branches_count', 'desc')
            ->orderBy('reviews_count', 'desc')
            ->get();
    }
}
