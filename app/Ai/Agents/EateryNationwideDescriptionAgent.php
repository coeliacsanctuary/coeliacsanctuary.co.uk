<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

class EateryNationwideDescriptionAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return view('prompts.nationwide-description')->render();
    }
}
