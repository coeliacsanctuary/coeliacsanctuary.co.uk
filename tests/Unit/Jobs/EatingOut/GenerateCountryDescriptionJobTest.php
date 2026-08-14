<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\EatingOut;

use App\Ai\Agents\EateryCountryDescriptionAgent;
use App\Jobs\EatingOut\GenerateCountryDescriptionJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Prompts\AgentPrompt;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenerateCountryDescriptionJobTest extends TestCase
{
    protected EateryCountry $country;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Bus::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->country = $this->build(EateryCountry::class)
            ->state(['id' => 2, 'country' => 'Wales'])
            ->create();

        $this->build(EateryCounty::class)
            ->state(['country_id' => $this->country->id])
            ->count(3)
            ->create()
            ->each(function (EateryCounty $county): void {
                $town = $this->build(EateryTown::class)
                    ->state(['county_id' => $county->id])
                    ->create();

                $this->build(Eatery::class)
                    ->state([
                        'country_id' => $this->country->id,
                        'county_id' => $county->id,
                        'town_id' => $town->id,
                    ])
                    ->count(2)
                    ->create();
            });
    }

    #[Test]
    public function itPromptsTheAgentWithTheCountryName(): void
    {
        EateryCountryDescriptionAgent::fake();

        (new GenerateCountryDescriptionJob($this->country))->handle();

        EateryCountryDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Country: Wales')
        );
    }

    #[Test]
    public function thePromptContainsTheCountyCount(): void
    {
        EateryCountryDescriptionAgent::fake();

        (new GenerateCountryDescriptionJob($this->country))->handle();

        EateryCountryDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Counties: 3')
        );
    }

    #[Test]
    public function thePromptContainsTheEateryCount(): void
    {
        EateryCountryDescriptionAgent::fake();

        (new GenerateCountryDescriptionJob($this->country))->handle();

        EateryCountryDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Number of Eateries: 6')
        );
    }

    #[Test]
    public function thePromptContainsTheAverageEateryRating(): void
    {
        EateryCountryDescriptionAgent::fake();

        $this->country->counties->each(function (EateryCounty $county): void {
            $this->build(EateryReview::class)
                ->approved()
                ->on($county->eateries()->first())
                ->create(['rating' => 4]);
        });

        (new GenerateCountryDescriptionJob($this->country))->handle();

        EateryCountryDescriptionAgent::assertPrompted(
            fn (AgentPrompt $prompt) => str_contains($prompt->prompt, 'Average Eatery Rating: 4.0')
        );
    }

    #[Test]
    public function itUpdatesTheCountryDescriptionWithTheAgentResponse(): void
    {
        EateryCountryDescriptionAgent::fake(['AI generated description']);

        (new GenerateCountryDescriptionJob($this->country))->handle();

        $this->assertEquals('AI generated description', $this->country->refresh()->description);
    }

    #[Test]
    public function itForgetsTheIndexCountsCache(): void
    {
        EateryCountryDescriptionAgent::fake();

        $key = config('coeliac.cacheable.eating-out.index-counts');

        Cache::put($key, 'foo');

        (new GenerateCountryDescriptionJob($this->country))->handle();

        $this->assertFalse(Cache::has($key));
    }

    #[Test]
    public function itDoesntPromptTheAgentForTheNationwideCountry(): void
    {
        EateryCountryDescriptionAgent::fake();

        $nationwide = $this->create(EateryCountry::class, ['country' => 'Nationwide']);

        (new GenerateCountryDescriptionJob($nationwide))->handle();

        EateryCountryDescriptionAgent::assertNeverPrompted();
    }

    #[Test]
    public function itDoesntUpdateTheDescriptionForTheNationwideCountry(): void
    {
        EateryCountryDescriptionAgent::fake(['AI generated description']);

        $nationwide = $this->create(EateryCountry::class, ['country' => 'Nationwide', 'description' => null]);

        (new GenerateCountryDescriptionJob($nationwide))->handle();

        $this->assertNull($nationwide->refresh()->description);
    }

    #[Test]
    public function itIsUniqueToTheCountry(): void
    {
        $this->assertEquals(
            $this->country->id,
            (new GenerateCountryDescriptionJob($this->country))->uniqueId()
        );
    }

    #[Test]
    public function itIsDelayed(): void
    {
        $this->assertNotNull((new GenerateCountryDescriptionJob($this->country))->delay);
    }
}
