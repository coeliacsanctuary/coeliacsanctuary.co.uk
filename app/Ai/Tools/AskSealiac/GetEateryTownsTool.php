<?php

declare(strict_types=1);

namespace App\Ai\Tools\AskSealiac;

use App\Models\EatingOut\EateryTown;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetEateryTownsTool extends BaseTool
{
    public function description(): Stringable|string
    {
        return 'Get all towns within a given county. If the county is London, the results will be London boroughs (eg City of Westminster) rather than traditional towns.';
    }

    protected function execute(Request $request): Stringable|string
    {
        return EateryTown::query()
            ->where('county_id', $request->integer('county_id'))
            ->get()
            ->map(fn (EateryTown $town): array => [
                'id' => $town->id,
                'name' => $town->town,
                'link' => $town->absoluteLink(),
            ])
            ->toJson();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'county_id' => $schema->integer()->required(),
        ];
    }
}
