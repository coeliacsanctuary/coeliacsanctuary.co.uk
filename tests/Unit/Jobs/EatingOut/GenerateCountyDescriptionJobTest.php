<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\EatingOut;

use App\Ai\Agents\EateryCountyDescriptionAgent;
use App\Jobs\EatingOut\GenerateCountyDescriptionJob;
use App\Models\EatingOut\Eatery;
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

class GenerateCountyDescriptionJobTest extends TestCase
{
    protected EateryCounty $county;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Bus::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->firstOrFail();

        $this->build(EateryTown::class)
            ->state(['county_id' => $this->county->id])
            ->count(3)
            ->create()
            ->each(function (EateryTown $town): void {
                $this->build(Eatery::class)
                    ->state([
                        'county_id' => $this->county->id,
                        'town_id' => $town->id,
                    ])
                    ->count(2)
                    ->create();
            });
    }

    #[Test]
    public function itPromptsTheAgentWithTheCountyName(): void
    {
        EateryCountyDescriptionAgent::fake();

        (new GenerateCountyDescriptionJob($this->county))->handle();

        EateryCountyDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'County: Cheshire')
        );
    }

    #[Test]
    public function thePromptContainsTheTownCount(): void
    {
        EateryCountyDescriptionAgent::fake();

        (new GenerateCountyDescriptionJob($this->county))->handle();

        EateryCountyDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Towns: 3')
        );
    }

    #[Test]
    public function thePromptContainsTheEateryCount(): void
    {
        EateryCountyDescriptionAgent::fake();

        (new GenerateCountyDescriptionJob($this->county))->handle();

        EateryCountyDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Eateries: 6')
        );
    }

    #[Test]
    public function thePromptCountsNationwideBranchesInTheEateryTotal(): void
    {
        EateryCountyDescriptionAgent::fake();

        $town = $this->county->activeTowns()->firstOrFail();

        $this->build(NationwideBranch::class)
            ->state([
                'county_id' => $this->county->id,
                'town_id' => $town->id,
            ])
            ->count(2)
            ->create();

        (new GenerateCountyDescriptionJob($this->county))->handle();

        EateryCountyDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Eateries: 8')
        );
    }

    #[Test]
    public function thePromptContainsTheAverageEateryRating(): void
    {
        EateryCountyDescriptionAgent::fake();

        $this->county->activeTowns->each(function (EateryTown $town): void {
            $this->build(EateryReview::class)
                ->approved()
                ->on($town->liveEateries()->firstOrFail())
                ->create(['rating' => 4]);
        });

        (new GenerateCountyDescriptionJob($this->county))->handle();

        EateryCountyDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Average Eatery Rating: 4.0')
        );
    }

    #[Test]
    public function itUpdatesTheCountyDescriptionWithTheAgentResponse(): void
    {
        EateryCountyDescriptionAgent::fake(['AI generated description']);

        (new GenerateCountyDescriptionJob($this->county))->handle();

        $this->assertEquals('AI generated description', $this->county->refresh()->description);
    }

    #[Test]
    public function itDoesntPromptTheAgentForTheNationwideCounty(): void
    {
        EateryCountyDescriptionAgent::fake();

        $nationwide = $this->create(EateryCounty::class, ['county' => 'Nationwide']);

        (new GenerateCountyDescriptionJob($nationwide))->handle();

        EateryCountyDescriptionAgent::assertNeverPrompted();
    }

    #[Test]
    public function itDoesntUpdateTheDescriptionForTheNationwideCounty(): void
    {
        EateryCountyDescriptionAgent::fake(['AI generated description']);

        $nationwide = $this->create(EateryCounty::class, ['county' => 'Nationwide', 'description' => null]);

        (new GenerateCountyDescriptionJob($nationwide))->handle();

        $this->assertNull($nationwide->refresh()->description);
    }

    #[Test]
    public function itIsUniqueToTheCounty(): void
    {
        $this->assertEquals(
            $this->county->id,
            (new GenerateCountyDescriptionJob($this->county))->uniqueId()
        );
    }

    #[Test]
    public function itIsDelayed(): void
    {
        $this->assertNotNull((new GenerateCountyDescriptionJob($this->county))->delay);
    }
}
