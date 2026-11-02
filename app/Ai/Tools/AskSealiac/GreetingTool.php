<?php

declare(strict_types=1);

namespace App\Ai\Tools\AskSealiac;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class GreetingTool extends BaseTool
{
    public function description(): Stringable|string
    {
        return 'Gives an introductory greeting to the user';
    }

    protected function execute(Request $request): Stringable|string
    {
        return $this->description();
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
