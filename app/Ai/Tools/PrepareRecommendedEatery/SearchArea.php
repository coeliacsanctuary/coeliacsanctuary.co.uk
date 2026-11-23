<?php

declare(strict_types=1);

namespace App\Ai\Tools\PrepareRecommendedEatery;

use App\Models\EatingOut\EateryArea;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchArea implements Tool
{
    public function description(): Stringable|string
    {
        return 'Search for an area in the given london borough';
    }

    public function handle(Request $request): Stringable|string
    {
        $search = EateryArea::query()
            ->withoutGlobalScopes()
            ->whereRelation('town', 'town', $request->string('town'))
            ->whereLike('area', "%{$request->string('area')}%")
            ->orderBy('area')
            ->pluck('area');

        return (string) json_encode($search);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'borough' => $schema->string()->required(),
            'area' => $schema->string()->required(),
        ];
    }
}
