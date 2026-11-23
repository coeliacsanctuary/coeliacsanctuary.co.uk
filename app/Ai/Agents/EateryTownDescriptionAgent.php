<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Ai\Tools\ListPopularEateriesInTown;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

class EateryTownDescriptionAgent implements Agent, HasTools
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return view('prompts.town-description')->render();
    }

    public function tools(): iterable
    {
        return [
            new ListPopularEateriesInTown(),
        ];
    }
}
