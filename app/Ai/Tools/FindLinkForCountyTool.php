<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\EatingOut\EateryCounty;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class FindLinkForCountyTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Attempt to find a link to the county page in the eating out guide of the website, using a whereLike on the database';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $county = EateryCounty::query()->whereLike('county', "%{$request->string('county')->toString()}%")->first();

        if ($county) {
            return $county->absoluteLink();
        }

        return '- county not found -';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'county' => $schema->string()->required(),
        ];
    }
}
