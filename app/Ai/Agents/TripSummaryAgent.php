<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class TripSummaryAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return view('prompts.trip-summary')->render();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'overview' => $schema->string()->required(),
            'stops' => $schema->array()
                ->items($schema->object(fn (JsonSchema $schema): array => [
                    'number' => $schema->integer()->required(),
                    'summary' => $schema->string()->required(),
                ]))
                ->required(),
        ];
    }
}
