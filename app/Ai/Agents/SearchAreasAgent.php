<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[Model('gpt-4o-mini')]
class SearchAreasAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return view('prompts.search')->render();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'shop' => $schema->integer()->required(),
            'eating-out' => $schema->integer()->required(),
            'blogs' => $schema->integer()->required(),
            'recipes' => $schema->integer()->required(),
            'location' => $schema->string()->nullable()->required(),
            'explanation' => $schema->string()->required(),
        ];
    }
}
