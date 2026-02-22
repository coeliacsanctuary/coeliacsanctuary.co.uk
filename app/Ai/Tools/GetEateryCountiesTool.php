<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\EatingOut\EateryCounty;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetEateryCountiesTool extends BaseTool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get all available eatery counties for a given country. Returns a list of counties with their id and name.';
    }

    /**
     * Execute the tool's logic.
     */
    protected function execute(Request $request): Stringable|string
    {
        return EateryCounty::query()
            ->where('country_id', $request->integer('country_id'))
            ->where('county', '!=', 'Nationwide')
            ->get()
            ->map(fn (EateryCounty $county) => [
                'id' => $county->id,
                'county' => $county->county,
                'link' => $county->absoluteLink(),
            ])
            ->toJson();
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'country_id' => $schema->integer()->required(),
        ];
    }
}
