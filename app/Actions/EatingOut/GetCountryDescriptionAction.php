<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Ai\Agents\EateryCountryDescriptionAgent;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\AgentResponse;

class GetCountryDescriptionAction
{
    public function handle(EateryCountry $country): string
    {
        if ($country->description) {
            return Str::inlineMarkdown($country->description);
        }

        $eateries = number_format($country->counties->sum(fn (EateryCounty $county) => $county->getAttribute('total_eateries_count') + $county->getAttribute('nationwide_branches_count')));
        $averageRating = number_format($country->counties->avg('avg_rating') ?? 0, 1);
        $countiesLabel = Str::plural('county', $country->counties->count());

        $prompt = <<<PROMPT
        Country: {$country->country}
        Number of Counties: {$country->counties->count()}
        Number of Eateries: {$eateries}
        Average Eatery Rating: {$averageRating}
        PROMPT;

        EateryCountryDescriptionAgent::make()->queue($prompt)->then(function (AgentResponse $response) use ($country): void {
            $country->update(['description' => (string) $response]);

            Cache::forget(config('coeliac.cacheable.eating-out.index-counts'));
        });

        $default = <<<DESC
        We've got **{$eateries}** gluten places to eat across **{$country->counties->count()}** {$countiesLabel}
        within {$country->country} listed in our eating out guide.
        DESC;

        return Str::inlineMarkdown($default);
    }
}
