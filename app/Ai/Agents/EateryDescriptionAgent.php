<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\EatingOut\Eatery;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[Model('gpt-4o-mini')]
class EateryDescriptionAgent implements Agent
{
    use Promptable;

    public function __construct(protected Eatery $eatery)
    {
        $this->eatery->loadMissing(['town']);
    }

    public function instructions(): Stringable|string
    {
        return view('prompts.eatery-description', [
            'eatery' => $this->eatery,
        ])->render();
    }
}
