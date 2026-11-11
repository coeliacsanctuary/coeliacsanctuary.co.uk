<?php

declare(strict_types=1);

namespace App\Ai\Tools\AskSealiac;

use App\Models\EatingOut\EateryCounty;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetEateryCountiesTool extends BaseTool
{
    public function description(): Stringable|string
    {
        return 'Get all available eatery counties for a given country. Returns a list of counties with their id and name.';
    }

    protected function execute(Request $request): Stringable|string
    {
        return EateryCounty::query()
            ->where('country_id', $request->integer('country_id'))
            ->where('county', '!=', 'Nationwide')
            ->get()
            ->map(fn (EateryCounty $county): array => [
                'id' => $county->id,
                'county' => $county->county,
                'link' => $county->absoluteLink(),
            ])
            ->toJson();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'country_id' => $schema->integer()->required(),
        ];
    }
}
