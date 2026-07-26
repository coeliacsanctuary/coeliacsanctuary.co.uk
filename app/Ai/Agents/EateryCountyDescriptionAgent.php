<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Tools\FindLinkForTownTool;
use App\Ai\Tools\ListPopularEateriesInCounty;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

class EateryCountyDescriptionAgent implements Agent, HasTools
{
    use Promptable;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return view('prompts.county-description')->render();
    }

    public function tools(): iterable
    {
        return [
            new FindLinkForTownTool(),
            new ListPopularEateriesInCounty(),
        ];
    }
}
