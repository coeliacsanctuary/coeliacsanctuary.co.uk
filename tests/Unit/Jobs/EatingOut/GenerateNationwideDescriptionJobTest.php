<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\EatingOut;

use App\Ai\Agents\EateryNationwideDescriptionAgent;
use App\Jobs\EatingOut\GenerateNationwideDescriptionJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCountry;
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

class GenerateNationwideDescriptionJobTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $town;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Bus::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        $country = $this->create(EateryCountry::class, ['country' => 'Nationwide']);

        $this->county = $this->create(EateryCounty::class, [
            'county' => 'Nationwide',
            'slug' => 'nationwide',
            'country_id' => $country->id,
        ]);

        $this->town = $this->create(EateryTown::class, [
            'town' => 'Nationwide',
            'county_id' => $this->county->id,
        ]);
    }

    protected function createChain(string $name, array $state = []): Eatery
    {
        return $this->build(Eatery::class)
            ->state([
                'name' => $name,
                'country_id' => $this->county->country_id,
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
            ])
            ->state($state)
            ->create();
    }

    #[Test]
    public function itPromptsTheAgentWithTheNumberOfChains(): void
    {
        EateryNationwideDescriptionAgent::fake();

        $this->createChain('Nandos');
        $this->createChain('Wagamama');

        (new GenerateNationwideDescriptionJob($this->county))->handle();

        EateryNationwideDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Chains: 2')
        );
    }

    #[Test]
    public function thePromptListsEachChainByName(): void
    {
        EateryNationwideDescriptionAgent::fake();

        $this->createChain('Nandos');
        $this->createChain('Wagamama');

        (new GenerateNationwideDescriptionJob($this->county))->handle();

        EateryNationwideDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Nandos')
                && str_contains($prompt->prompt, 'Wagamama')
        );
    }

    #[Test]
    public function thePromptContainsTheBranchCountsForChainsWithBranches(): void
    {
        EateryNationwideDescriptionAgent::fake();

        $chain = $this->createChain('Nandos');

        $this->build(NationwideBranch::class)
            ->state([
                'wheretoeat_id' => $chain->id,
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
            ])
            ->count(3)
            ->create();

        (new GenerateNationwideDescriptionJob($this->county))->handle();

        EateryNationwideDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Chains with Branch Listings: 1')
                && str_contains($prompt->prompt, 'Total Branches Listed: 3')
                && str_contains($prompt->prompt, 'Branches Listed: 3')
        );
    }

    #[Test]
    public function chainsWithoutBranchesHaveNoBranchCountInThePrompt(): void
    {
        EateryNationwideDescriptionAgent::fake();

        $this->createChain('Nandos');

        (new GenerateNationwideDescriptionJob($this->county))->handle();

        EateryNationwideDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Chains with Branch Listings: 0')
                && ! str_contains($prompt->prompt, "\nBranches Listed:")
        );
    }

    #[Test]
    public function thePromptContainsTheRatingForAChainWithEnoughReviews(): void
    {
        EateryNationwideDescriptionAgent::fake();

        $chain = $this->createChain('Nandos');

        $this->build(EateryReview::class)
            ->approved()
            ->on($chain)
            ->count(5)
            ->create(['rating' => 4]);

        (new GenerateNationwideDescriptionJob($this->county))->handle();

        EateryNationwideDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Rating: 4.0 from 5 reviews')
        );
    }

    #[Test]
    public function thePromptOmitsTheRatingForAChainBelowTheReviewThreshold(): void
    {
        EateryNationwideDescriptionAgent::fake();

        $chain = $this->createChain('Nandos');

        $this->build(EateryReview::class)
            ->approved()
            ->on($chain)
            ->count(2)
            ->create(['rating' => 5]);

        (new GenerateNationwideDescriptionJob($this->county))->handle();

        EateryNationwideDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => ! str_contains($prompt->prompt, 'Rating:')
        );
    }

    #[Test]
    public function itUpdatesTheCountyDescriptionWithTheAgentResponse(): void
    {
        EateryNationwideDescriptionAgent::fake(['AI generated description']);

        $this->createChain('Nandos');

        (new GenerateNationwideDescriptionJob($this->county))->handle();

        $this->assertEquals('AI generated description', $this->county->refresh()->description);
    }

    #[Test]
    public function itDoesntPromptTheAgentForACountyOutsideTheNationwideCountry(): void
    {
        EateryNationwideDescriptionAgent::fake();

        $county = EateryCounty::query()->withoutGlobalScopes()->firstOrFail();

        (new GenerateNationwideDescriptionJob($county))->handle();

        EateryNationwideDescriptionAgent::assertNeverPrompted();
    }

    #[Test]
    public function itDoesntUpdateTheDescriptionForACountyOutsideTheNationwideCountry(): void
    {
        EateryNationwideDescriptionAgent::fake(['AI generated description']);

        $county = EateryCounty::query()->withoutGlobalScopes()->firstOrFail();

        $county->update(['description' => null]);

        (new GenerateNationwideDescriptionJob($county))->handle();

        $this->assertNull($county->refresh()->description);
    }

    #[Test]
    public function itIsUniqueToTheCounty(): void
    {
        $this->assertEquals(
            $this->county->id,
            (new GenerateNationwideDescriptionJob($this->county))->uniqueId()
        );
    }

    #[Test]
    public function itIsDelayed(): void
    {
        $this->assertNotNull((new GenerateNationwideDescriptionJob($this->county))->delay);
    }
}
