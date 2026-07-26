<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\EatingOut;

use App\Ai\Agents\EateryBoroughDescriptionAgent;
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

class GenerateBoroughDescriptionJobTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $borough;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Bus::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = $this->create(EateryCounty::class, ['county' => 'London']);
        $this->borough = $this->create(EateryTown::class, ['county_id' => $this->county->id, 'town' => 'Camden']);

        $this->build(EateryArea::class)
            ->state(['town_id' => $this->borough->id])
            ->count(3)
            ->create()
            ->each(function (EateryArea $area): void {
                $this->build(Eatery::class)
                    ->state([
                        'county_id' => $this->county->id,
                        'town_id' => $this->borough->id,
                        'area_id' => $area->id,
                    ])
                    ->count(2)
                    ->create();
            });
    }

    /** The town needs a live eatery, otherwise the hasPlaces scope hides its county. */
    protected function createNonLondonTown(): EateryTown
    {
        $county = $this->create(EateryCounty::class, ['county' => 'Cheshire East']);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id, 'description' => null]);

        $this->create(Eatery::class, ['county_id' => $county->id, 'town_id' => $town->id]);

        return $town;
    }

    #[Test]
    public function itPromptsTheAgentWithTheBoroughName(): void
    {
        EateryBoroughDescriptionAgent::fake();

        (new GenerateBoroughDescriptionJob($this->borough))->handle();

        EateryBoroughDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Borough: Camden')
        );
    }

    #[Test]
    public function thePromptContainsTheAreaCount(): void
    {
        EateryBoroughDescriptionAgent::fake();

        (new GenerateBoroughDescriptionJob($this->borough))->handle();

        EateryBoroughDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Areas: 3')
        );
    }

    #[Test]
    public function thePromptContainsTheEateryCount(): void
    {
        EateryBoroughDescriptionAgent::fake();

        (new GenerateBoroughDescriptionJob($this->borough))->handle();

        EateryBoroughDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Eateries: 6')
        );
    }

    #[Test]
    public function thePromptDoesntCountEateriesThatArentLive(): void
    {
        EateryBoroughDescriptionAgent::fake();

        $this->create(Eatery::class, [
            'county_id' => $this->county->id,
            'town_id' => $this->borough->id,
            'live' => false,
        ]);

        (new GenerateBoroughDescriptionJob($this->borough))->handle();

        EateryBoroughDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Eateries: 6')
        );
    }

    #[Test]
    public function thePromptCountsNationwideBranchesInTheEateryTotal(): void
    {
        EateryBoroughDescriptionAgent::fake();

        $this->build(NationwideBranch::class)
            ->state([
                'county_id' => $this->county->id,
                'town_id' => $this->borough->id,
            ])
            ->count(2)
            ->create();

        (new GenerateBoroughDescriptionJob($this->borough))->handle();

        EateryBoroughDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Eateries: 8')
        );
    }

    #[Test]
    public function thePromptContainsTheAverageEateryRating(): void
    {
        EateryBoroughDescriptionAgent::fake();

        $this->borough->liveEateries->each(function (Eatery $eatery): void {
            $this->build(EateryReview::class)
                ->approved()
                ->on($eatery)
                ->create(['rating' => 4]);
        });

        (new GenerateBoroughDescriptionJob($this->borough))->handle();

        EateryBoroughDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Average Eatery Rating: 4.0')
        );
    }

    #[Test]
    public function itUpdatesTheBoroughDescriptionWithTheAgentResponse(): void
    {
        EateryBoroughDescriptionAgent::fake(['AI generated description']);

        (new GenerateBoroughDescriptionJob($this->borough))->handle();

        $this->assertEquals('AI generated description', $this->borough->refresh()->description);
    }

    #[Test]
    public function itDoesntPromptTheAgentForANonLondonBorough(): void
    {
        EateryBoroughDescriptionAgent::fake();

        (new GenerateBoroughDescriptionJob($this->createNonLondonTown()))->handle();

        EateryBoroughDescriptionAgent::assertNeverPrompted();
    }

    #[Test]
    public function itDoesntUpdateTheDescriptionForANonLondonBorough(): void
    {
        EateryBoroughDescriptionAgent::fake(['AI generated description']);

        $town = $this->createNonLondonTown();

        (new GenerateBoroughDescriptionJob($town))->handle();

        $this->assertNull($town->refresh()->description);
    }

    #[Test]
    public function itIsUniqueToTheBorough(): void
    {
        $this->assertEquals(
            $this->borough->id,
            (new GenerateBoroughDescriptionJob($this->borough))->uniqueId()
        );
    }

    #[Test]
    public function itIsDelayed(): void
    {
        $this->assertNotNull((new GenerateBoroughDescriptionJob($this->borough))->delay);
    }
}
