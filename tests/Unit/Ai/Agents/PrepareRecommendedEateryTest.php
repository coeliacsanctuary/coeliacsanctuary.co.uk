<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\PrepareRecommendedEatery;
use App\Ai\Tools\PrepareRecommendedEatery\EateryCuisineList;
use App\Ai\Tools\PrepareRecommendedEatery\EateryFeatureList;
use App\Ai\Tools\PrepareRecommendedEatery\EateryInfoExamples;
use App\Ai\Tools\PrepareRecommendedEatery\EateryVenueTypeList;
use App\Ai\Tools\PrepareRecommendedEatery\GeoLookup;
use App\Ai\Tools\PrepareRecommendedEatery\SearchArea;
use App\Ai\Tools\PrepareRecommendedEatery\SearchCounty;
use App\Ai\Tools\PrepareRecommendedEatery\SearchTown;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Providers\Tools\WebFetch;
use Laravel\Ai\Providers\Tools\WebSearch;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PrepareRecommendedEateryTest extends TestCase
{
    protected PrepareRecommendedEatery $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = new PrepareRecommendedEatery();
    }

    #[Test]
    public function itRendersInstructions(): void
    {
        $this->assertNotEmpty((string) $this->agent->instructions());
    }

    #[Test]
    public function theInstructionsMentionCoeliacSanctuary(): void
    {
        $this->assertStringContainsString('Coeliac Sanctuary', (string) $this->agent->instructions());
    }

    #[Test]
    public function theInstructionsMentionGlutenFree(): void
    {
        $this->assertStringContainsString('gluten free', (string) $this->agent->instructions());
    }

    #[Test]
    public function theInstructionsMentionTheSearchTools(): void
    {
        $instructions = (string) $this->agent->instructions();

        $this->assertStringContainsString('SearchCounty', $instructions);
        $this->assertStringContainsString('SearchTown', $instructions);
        $this->assertStringContainsString('SearchArea', $instructions);
        $this->assertStringContainsString('GeoLookup', $instructions);
    }

    #[Test]
    public function theInstructionsMentionTheListTools(): void
    {
        $instructions = (string) $this->agent->instructions();

        $this->assertStringContainsString('EateryVenueTypeList', $instructions);
        $this->assertStringContainsString('EateryCuisineList', $instructions);
        $this->assertStringContainsString('EateryInfoExamples', $instructions);
        $this->assertStringContainsString('EateryFeatureList', $instructions);
    }

    #[Test]
    public function itImplementsHasStructuredOutput(): void
    {
        $this->assertInstanceOf(HasStructuredOutput::class, $this->agent);
    }

    #[Test]
    public function itImplementsHasTools(): void
    {
        $this->assertInstanceOf(HasTools::class, $this->agent);
    }

    #[Test]
    public function itReturnsTheExpectedTools(): void
    {
        $tools = collect($this->agent->tools());

        $this->assertCount(10, $tools);

        $this->assertEquals([
            WebSearch::class,
            WebFetch::class,
            SearchCounty::class,
            SearchTown::class,
            SearchArea::class,
            GeoLookup::class,
            EateryVenueTypeList::class,
            EateryCuisineList::class,
            EateryInfoExamples::class,
            EateryFeatureList::class,
        ], $tools->map(fn ($tool) => $tool::class)->values()->all());
    }
}
