<?php

declare(strict_types=1);

namespace App\Jobs\EatingOut;

use App\Ai\Agents\EateryCountryDescriptionAgent;
use App\Models\EatingOut\EateryCountry;
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
use Illuminate\Support\Facades\Cache;

#[Tries(1)]
#[Timeout(300)]
class GenerateCountryDescriptionJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 600;

    public function __construct(protected EateryCountry $country)
    {
        $this->delay(now()->addSeconds(10));
    }

    public function uniqueId(): int
    {
        return $this->country->id;
    }

    public function handle(): void
    {
        $response = EateryCountryDescriptionAgent::make()->prompt($this->buildPrompt());

        $this->country->update(['description' => $response->text]);

        Cache::forget(config('coeliac.cacheable.eating-out.index-counts'));
    }

    protected function buildPrompt(): string
    {
        $counties = $this->getCounties();

        $eateries = number_format($counties->sum(fn (EateryCounty $county) => $county->getAttribute('total_eateries_count') + $county->getAttribute('nationwide_branches_count')));
        $averageRating = number_format($counties->avg('avg_rating') ?? 0, 1);

        return <<<PROMPT
        Country: {$this->country->country}
        Number of Counties: {$counties->count()}
        Number of Eateries: {$eateries}
        Average Eatery Rating: {$averageRating}
        PROMPT;
    }

    /** @return Collection<int, EateryCounty> */
    protected function getCounties(): Collection
    {
        return $this->country
            ->counties()
            ->withCount(['eateries as total_eateries_count', 'nationwideBranches as nationwide_branches_count'])
            ->withAvg('reviews as avg_rating', 'rating')
            ->get();
    }
}
