<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Illuminate\Support\Collection;
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

    /** @return Collection<int, non-empty-string> */
    public function lookup(string $searchTerm): Collection
    {
        /** @var StructuredAgentResponse $response */
        $response = $this->prompt($searchTerm);

        /** @var array<int, string> $results */
        $results = $response['results'];

        return collect($results)
            ->map(fn (string $result) => mb_trim($result))
            ->filter(fn (string $result) => mb_strlen($result) > 0)
            ->values();
    }
}
