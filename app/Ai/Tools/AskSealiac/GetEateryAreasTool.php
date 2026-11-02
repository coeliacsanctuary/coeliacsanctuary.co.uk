<?php

declare(strict_types=1);

namespace App\Ai\Tools\AskSealiac;

use App\Models\EatingOut\EateryArea;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetEateryAreasTool extends BaseTool
{
    public function description(): Stringable|string
    {
        return 'Get all areas within a London borough. Only applicable when the county is London. For example, the borough (town) "City of Westminster" contains areas like "Leicester Square".';
    }

    protected function execute(Request $request): Stringable|string
    {
        return EateryArea::query()
            ->where('town_id', $request->integer('town_id'))
            ->get()
            ->map(fn (EateryArea $area): array => [
                'id' => $area->id,
                'name' => $area->area,
                'link' => $area->absoluteLink(),
            ])
            ->toJson();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'town_id' => $schema->integer()->required()->description('The ID of the town (aka london borough) to get areas for.'),
        ];
    }
}
