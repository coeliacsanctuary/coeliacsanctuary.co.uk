<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\EatingOut;

use App\Ai\Agents\EateryAreaDescriptionAgent;
use App\Ai\Agents\EateryBoroughDescriptionAgent;
use App\Jobs\EatingOut\GenerateAreaDescriptionJob;
use App\Jobs\EatingOut\GenerateBoroughDescriptionJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Prompts\AgentPrompt;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenerateAreaDescriptionJobTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $borough;

    protected EateryArea $area;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Bus::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = $this->create(EateryCounty::class, ['county' => 'London']);
        $this->borough = $this->create(EateryTown::class, ['county_id' => $this->county->id, 'town' => 'Camden']);
        $this->area = $this->create(EateryArea::class, ['town_id' => $this->borough->id, 'area' => 'Camden Lock']);

        $this->build(Eatery::class)
            ->state([
                'county_id' => $this->county->id,
                'town_id' => $this->borough->id,
                'area_id' => $this->area->id,
            ])
            ->count(6)
            ->create();
    }

    #[Test]
    public function itPromptsTheAgentWithTheAreaName(): void
    {
        EateryAreaDescriptionAgent::fake();

        (new GenerateAreaDescriptionJob($this->area))->handle();

        EateryAreaDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Area: Camden Lock')
        );
    }

    #[Test]
    public function thePromptContainsTheBorough(): void
    {
        EateryAreaDescriptionAgent::fake();

        (new GenerateAreaDescriptionJob($this->area))->handle();

        EateryAreaDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Borough: Camden')
        );
    }

    #[Test]
    public function thePromptContainsTheEateryCount(): void
    {
        EateryAreaDescriptionAgent::fake();

        (new GenerateAreaDescriptionJob($this->area))->handle();

        EateryAreaDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Eateries: 6')
        );
    }

    #[Test]
    public function thePromptDoesntCountEateriesThatArentLive(): void
    {
        EateryAreaDescriptionAgent::fake();

        $this->create(Eatery::class, [
            'county_id' => $this->county->id,
            'town_id' => $this->borough->id,
            'area_id' => $this->area->id,
            'live' => false,
        ]);

        (new GenerateAreaDescriptionJob($this->area))->handle();

        EateryAreaDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Eateries: 6')
        );
    }

    #[Test]
    public function thePromptCountsNationwideBranchesInTheEateryTotal(): void
    {
        EateryAreaDescriptionAgent::fake();

        $this->build(NationwideBranch::class)
            ->state([
                'county_id' => $this->county->id,
                'town_id' => $this->borough->id,
                'area_id' => $this->area->id,
            ])
            ->count(2)
            ->create();

        (new GenerateAreaDescriptionJob($this->area))->handle();

        EateryAreaDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Eateries: 8')
        );
    }

    #[Test]
    public function thePromptContainsTheAverageEateryRating(): void
    {
        EateryAreaDescriptionAgent::fake();

        $this->area->liveEateries->each(function (Eatery $eatery): void {
            $this->build(EateryReview::class)
                ->approved()
                ->on($eatery)
                ->create(['rating' => 4]);
        });

        (new GenerateAreaDescriptionJob($this->area))->handle();

        EateryAreaDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Average Eatery Rating: 4.0')
        );
    }

    #[Test]
    public function itUpdatesTheAreaDescriptionWithTheAgentResponse(): void
    {
        EateryAreaDescriptionAgent::fake(['AI generated description']);

        (new GenerateAreaDescriptionJob($this->area))->handle();

        $this->assertEquals('AI generated description', $this->area->refresh()->description);
    }

    #[Test]
    public function itIsUniqueToTheArea(): void
    {
        $this->assertEquals(
            $this->area->id,
            (new GenerateAreaDescriptionJob($this->area))->uniqueId()
        );
    }

    #[Test]
    public function itIsDelayed(): void
    {
        $this->assertNotNull((new GenerateAreaDescriptionJob($this->area))->delay);
    }
}
