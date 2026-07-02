<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Tools\PrepareRecommendedEatery\EateryCuisineList;
use App\Ai\Tools\PrepareRecommendedEatery\EateryFeatureList;
use App\Ai\Tools\PrepareRecommendedEatery\EateryInfoExamples;
use App\Ai\Tools\PrepareRecommendedEatery\EateryVenueTypeList;
use App\Ai\Tools\PrepareRecommendedEatery\GeoLookup;
use App\Ai\Tools\PrepareRecommendedEatery\SearchArea;
use App\Ai\Tools\PrepareRecommendedEatery\SearchCounty;
use App\Ai\Tools\PrepareRecommendedEatery\SearchTown;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\WebFetch;
use Laravel\Ai\Providers\Tools\WebSearch;
use Stringable;

#[Provider(Lab::Anthropic)]
#[Model('claude-opus-4-6')]
class PrepareRecommendedEatery implements Agent, HasStructuredOutput, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return view('prompts.prepare-recommended-eatery-base-instructions')->render();
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new WebSearch(),
            new WebFetch(),
            new SearchCounty(),
            new SearchTown(),
            new SearchArea(),
            new GeoLookup(),
            new EateryVenueTypeList(),
            new EateryCuisineList(),
            new EateryInfoExamples(),
            new EateryFeatureList(),
        ];
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'data' => $schema->object(fn (JsonSchema $schema) => [
                'place_name' => $schema->string()->required()->nullable(),
                'place_address' => $schema->string()->required()->nullable(),
                'place_country' => $schema->string()->enum(['England', 'Scotland', 'Wales', 'Northern Ireland', 'Republic of Ireland', 'Isle of Man', 'Chanel Islands'])->required()->nullable(),
                'place_county' => $schema->string()->required()->nullable(),
                'place_town' => $schema->string()->required()->nullable(),
                'place_area' => $schema->string()->required()->nullable(),
                'latitude' => $schema->number()->required()->nullable(),
                'longitude' => $schema->number()->required()->nullable(),
                'phone_number' => $schema->string()->required()->nullable(),
                'website' => $schema->string()->required()->nullable(),
                'facebook' => $schema->string()->required()->nullable(),
                'instagram' => $schema->string()->required()->nullable(),
                'eatery_Type' => $schema->string()->enum(['Eatery', 'Attraction', 'Hotel'])->required()->nullable(),
                'venue_type' => $schema->string()->required()->nullable(),
                'cuisine' => $schema->string()->required()->nullable(),
                'info' => $schema->string()->required()->nullable(),
                'features' => $schema->array()->items($schema->string())->required()->nullable(),
            ])->required()->nullable(),
            'explanation' => $schema->string()->required()->description('Please include your reasoning, sources, etc that lead you to build the data out.'),
            'is_eligible' => $schema->boolean()->required()->description('Whether the recommendation is eligible for adding to the website, if you found no proof that it does gluten free, then this would be a false.'),
        ];
    }
}
