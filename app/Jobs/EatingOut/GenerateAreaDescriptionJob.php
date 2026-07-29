<?php

declare(strict_types=1);

namespace App\Jobs\EatingOut;

use App\Ai\Agents\EateryAreaDescriptionAgent;
use App\Ai\Agents\EateryTownDescriptionAgent;
use App\Models\EatingOut\EateryArea;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

#[Tries(1)]
#[Timeout(300)]
class GenerateAreaDescriptionJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $uniqueFor = 600;

    public function __construct(protected EateryArea $area)
    {
        $this->delay(now()->addSeconds(10));
    }

    public function uniqueId(): int
    {
        return $this->area->id;
    }

    public function handle(): void
    {
        $response = EateryAreaDescriptionAgent::make()->prompt($this->buildPrompt());

        $this->area->update(['description' => $response->text]);
    }

    protected function buildPrompt(): string
    {
        $this->area->loadCount(['liveEateries', 'liveBranches']);

        $eateries = number_format($this->area->live_eateries_count + $this->area->live_branches_count);
        $averageRating = number_format($this->area->reviews()->avg('rating') ?? 0, 1);

        return <<<PROMPT
        Area: {$this->area->area}
        Borough: {$this->area->town?->town}
        Number of Eateries: {$eateries}
        Average Eatery Rating: {$averageRating}
        PROMPT;
    }
}
