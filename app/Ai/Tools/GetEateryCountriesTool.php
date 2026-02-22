<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\EatingOut\EateryCountry;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetEateryCountriesTool extends BaseTool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Get all available eatery countries in the system. Returns a list of countries with their id and name. Use this to find out which countries have eateries listed.';
    }

    /**
     * Execute the tool's logic.
     */
    protected function execute(Request $request): Stringable|string
    {
        return EateryCountry::query()
            ->where('country', '!=', 'Nationwide')
            ->get(['id', 'country'])
            ->toJson();
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
