<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\EatingOut;

use App\Ai\Agents\EateryTownDescriptionAgent;
use App\Jobs\EatingOut\GenerateTownDescriptionJob;
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

class GenerateTownDescriptionJobTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $town;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Bus::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = $this->create(EateryCounty::class, ['county' => 'Cheshire']);
        $this->town = $this->create(EateryTown::class, ['county_id' => $this->county->id, 'town' => 'Crewe']);

        $this->build(Eatery::class)
            ->state([
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
            ])
            ->count(6)
            ->create();
    }

    /** The town needs a live eatery, otherwise the hasPlaces scope hides its county. */
    protected function createLondonBorough(): EateryTown
    {
        $county = $this->create(EateryCounty::class, ['county' => 'London']);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id, 'description' => null]);

        $this->create(Eatery::class, ['county_id' => $county->id, 'town_id' => $town->id]);

        return $town;
    }

    #[Test]
    public function itPromptsTheAgentWithTheTownName(): void
    {
        EateryTownDescriptionAgent::fake();

        (new GenerateTownDescriptionJob($this->town))->handle();

        EateryTownDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Town: Crewe')
        );
    }

    #[Test]
    public function thePromptContainsTheEateryCount(): void
    {
        EateryTownDescriptionAgent::fake();

        (new GenerateTownDescriptionJob($this->town))->handle();

        EateryTownDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Eateries: 6')
        );
    }

    #[Test]
    public function thePromptDoesntCountEateriesThatArentLive(): void
    {
        EateryTownDescriptionAgent::fake();

        $this->create(Eatery::class, [
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
            'live' => false,
        ]);

        (new GenerateTownDescriptionJob($this->town))->handle();

        EateryTownDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Eateries: 6')
        );
    }

    #[Test]
    public function thePromptCountsNationwideBranchesInTheEateryTotal(): void
    {
        EateryTownDescriptionAgent::fake();

        $this->build(NationwideBranch::class)
            ->state([
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
            ])
            ->count(2)
            ->create();

        (new GenerateTownDescriptionJob($this->town))->handle();

        EateryTownDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Eateries: 8')
        );
    }

    #[Test]
    public function thePromptContainsTheAverageEateryRating(): void
    {
        EateryTownDescriptionAgent::fake();

        $this->town->liveEateries->each(function (Eatery $eatery): void {
            $this->build(EateryReview::class)
                ->approved()
                ->on($eatery)
                ->create(['rating' => 4]);
        });

        (new GenerateTownDescriptionJob($this->town))->handle();

        EateryTownDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Average Eatery Rating: 4.0')
        );
    }

    #[Test]
    public function itUpdatesTheTownDescriptionWithTheAgentResponse(): void
    {
        EateryTownDescriptionAgent::fake(['AI generated description']);

        (new GenerateTownDescriptionJob($this->town))->handle();

        $this->assertEquals('AI generated description', $this->town->refresh()->description);
    }

    #[Test]
    public function itDoesntPromptTheAgentForALondonBorough(): void
    {
        EateryTownDescriptionAgent::fake();

        (new GenerateTownDescriptionJob($this->createLondonBorough()))->handle();

        EateryTownDescriptionAgent::assertNeverPrompted();
    }

    #[Test]
    public function itDoesntUpdateTheDescriptionForLondonBorough(): void
    {
        EateryTownDescriptionAgent::fake(['AI generated description']);

        $town = $this->createLondonBorough();

        (new GenerateTownDescriptionJob($town))->handle();

        $this->assertNull($town->refresh()->description);
    }

    #[Test]
    public function itIsUniqueToTheTown(): void
    {
        $this->assertEquals(
            $this->town->id,
            (new GenerateTownDescriptionJob($this->town))->uniqueId()
        );
    }

    #[Test]
    public function itIsDelayed(): void
    {
        $this->assertNotNull((new GenerateTownDescriptionJob($this->town))->delay);
    }
}
