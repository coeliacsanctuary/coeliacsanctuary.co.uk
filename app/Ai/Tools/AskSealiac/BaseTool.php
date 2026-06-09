<?php

declare(strict_types=1);

namespace App\Ai\Tools\AskSealiac;

use App\Ai\State\ChatContext;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

abstract class BaseTool implements Tool
{
    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        ChatContext::addToolUse(class_basename($this), $request->all());

        return $this->execute($request);
    }

    /**
     * Execute the tool's logic.
     */
    abstract protected function execute(Request $request): Stringable|string;
}
