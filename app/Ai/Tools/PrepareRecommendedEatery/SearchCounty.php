<?php

declare(strict_types=1);

namespace App\Ai\Tools\PrepareRecommendedEatery;

use App\Models\EatingOut\EateryCounty;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchCounty implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search for a county in the given country';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $search = EateryCounty::query()
            ->withoutGlobalScopes()
            ->whereRelation('country', 'country', $request->string('country'))
            ->whereLike('county', "%{$request->string('county')}%")
            ->orderBy('county')
            ->pluck('county');

        return (string) json_encode($search);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'country' => $schema->string()->required(),
            'county' => $schema->string()->required(),
        ];
    }
}
