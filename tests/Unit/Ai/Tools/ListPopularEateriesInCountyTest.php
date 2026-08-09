<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\ListPopularEateriesInCounty;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListPopularEateriesInCountyTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $town;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = $this->create(EateryCounty::class, ['county' => 'Cheshire East']);
        $this->town = $this->create(EateryTown::class, ['county_id' => $this->county->id, 'town' => 'Macclesfield']);
    }

    protected function createEatery(int $rating = 5, int $reviews = 1, array $attributes = []): Eatery
    {
        $eatery = $this->create(Eatery::class, [
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
            ...$attributes,
        ]);

        $this->build(EateryReview::class)
            ->approved()
            ->count($reviews)
            ->create(['wheretoeat_id' => $eatery->id, 'rating' => $rating]);

        return $eatery;
    }

    protected function handleTool(string $county = 'Cheshire East'): array
    {
        return json_decode((string) (new ListPopularEateriesInCounty())->handle(new Request(['county' => $county])), true);
    }

    #[Test]
    public function itReturnsTheEateriesInTheGivenCounty(): void
    {
        $eatery = $this->createEatery(rating: 4, reviews: 2, attributes: ['name' => 'Test Cafe']);

        $result = $this->handleTool();

        $this->assertCount(1, $result);
        $this->assertEquals('Test Cafe', $result[0]['name']);
        $this->assertEquals('Macclesfield', $result[0]['town']);
        $this->assertEquals($eatery->address, $result[0]['address']);
        $this->assertEquals('4', $result[0]['average_rating']);
        $this->assertEquals(2, $result[0]['total_reviews']);
        $this->assertEquals($eatery->absoluteLink(), $result[0]['link']);
    }

    #[Test]
    public function itOrdersTheEateriesByTheirAverageRating(): void
    {
        $this->createEatery(rating: 3, attributes: ['name' => 'Average Cafe']);
        $this->createEatery(rating: 5, attributes: ['name' => 'Great Cafe']);
        $this->createEatery(rating: 4, attributes: ['name' => 'Good Cafe']);

        $result = $this->handleTool();

        $this->assertEquals(['Great Cafe', 'Good Cafe', 'Average Cafe'], array_column($result, 'name'));
    }

    #[Test]
    public function itOrdersEateriesWithTheSameRatingByTheirNumberOfReviews(): void
    {
        $this->createEatery(rating: 5, reviews: 1, attributes: ['name' => 'Quiet Cafe']);
        $this->createEatery(rating: 5, reviews: 3, attributes: ['name' => 'Busy Cafe']);

        $result = $this->handleTool();

        $this->assertEquals(['Busy Cafe', 'Quiet Cafe'], array_column($result, 'name'));
    }

    #[Test]
    public function itOnlyReturnsTheTopFiveEateries(): void
    {
        foreach (range(1, 7) as $index) {
            $this->createEatery(attributes: ['name' => "Cafe {$index}"]);
        }

        $this->assertCount(5, $this->handleTool());
    }

    #[Test]
    public function itDoesntReturnEateriesFromOtherCounties(): void
    {
        $this->createEatery(attributes: ['name' => 'Local Cafe']);

        $otherCounty = $this->create(EateryCounty::class, ['county' => 'Dorset']);
        $otherTown = $this->create(EateryTown::class, ['county_id' => $otherCounty->id]);

        $this->create(Eatery::class, [
            'name' => 'Away Cafe',
            'county_id' => $otherCounty->id,
            'town_id' => $otherTown->id,
        ]);

        $result = $this->handleTool();

        $this->assertCount(1, $result);
        $this->assertEquals('Local Cafe', $result[0]['name']);
    }

    #[Test]
    public function itDoesntReturnEateriesThatArentLive(): void
    {
        $this->createEatery(attributes: ['name' => 'Live Cafe']);
        $this->createEatery(attributes: ['name' => 'Hidden Cafe', 'live' => false]);

        $result = $this->handleTool();

        $this->assertCount(1, $result);
        $this->assertEquals('Live Cafe', $result[0]['name']);
    }

    #[Test]
    public function itDoesntReturnEateriesThatHaveClosedDown(): void
    {
        $this->createEatery(attributes: ['name' => 'Open Cafe']);
        $this->createEatery(attributes: ['name' => 'Closed Cafe', 'closed_down' => true]);

        $result = $this->handleTool();

        $this->assertCount(1, $result);
        $this->assertEquals('Open Cafe', $result[0]['name']);
    }

    #[Test]
    public function itThrowsAnExceptionWhenTheCountyDoesntExist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->handleTool('Nonexistent County');
    }

    #[Test]
    public function itHasTheCorrectSchema(): void
    {
        $schema = (new ListPopularEateriesInCounty())->schema(new JsonSchemaTypeFactory());

        $this->assertArrayHasKey('county', $schema);
    }
}
