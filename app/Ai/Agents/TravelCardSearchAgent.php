<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Stringable;

#[Model('gpt-4o-mini')]
class TravelCardSearchAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return view('prompts.travel-card-lookup')->render();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'results' => $schema->array()->items($schema->string())->required(),
            'explanation' => $schema->string()->required(),
        ];
    }

    public function lookup(string $searchTerm): ?string
    {
        /** @var StructuredAgentResponse $response */
        $response = $this->prompt($searchTerm);

        /** @var array<int, string> $results */
        $results = $response['results'];

        return collect($results)->first();
    }
}
