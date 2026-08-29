<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\PrepareRecommendedEatery;

use App\Ai\Tools\PrepareRecommendedEatery\SearchArea;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SearchAreaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itReturnsAreasMatchingTheSearch(): void
    {
        $county = $this->create(EateryCounty::class, ['country_id' => 1]);
        $town = $this->create(EateryTown::class, ['town' => 'City of Westminster', 'county_id' => $county->id]);
        $this->create(EateryArea::class, ['area' => 'Soho', 'town_id' => $town->id]);
        $this->build(Eatery::class)->createQuietly(['town_id' => $town->id, 'county_id' => $county->id]);

        $result = json_decode((string) (new SearchArea())->handle(new Request([
            'town' => 'City of Westminster',
            'area' => 'Soho',
        ])), true);

        $this->assertContains('Soho', $result);
    }

    #[Test]
    public function itDoesAPartialMatch(): void
    {
        $county = $this->create(EateryCounty::class, ['country_id' => 1]);
        $town = $this->create(EateryTown::class, ['town' => 'City of Westminster', 'county_id' => $county->id]);
        $this->create(EateryArea::class, ['area' => 'Soho', 'town_id' => $town->id]);
        $this->build(Eatery::class)->createQuietly(['town_id' => $town->id, 'county_id' => $county->id]);

        $result = json_decode((string) (new SearchArea())->handle(new Request([
            'town' => 'City of Westminster',
            'area' => 'Soh',
        ])), true);

        $this->assertContains('Soho', $result);
    }

    #[Test]
    public function itOnlyReturnsAreasForTheGivenTown(): void
    {
        $county = $this->create(EateryCounty::class, ['country_id' => 1]);

        $westminster = $this->create(EateryTown::class, ['town' => 'City of Westminster', 'county_id' => $county->id]);
        $this->create(EateryArea::class, ['area' => 'Soho', 'town_id' => $westminster->id]);
        $this->build(Eatery::class)->createQuietly(['town_id' => $westminster->id, 'county_id' => $county->id]);

        $camden = $this->create(EateryTown::class, ['town' => 'London Borough of Camden', 'county_id' => $county->id]);
        $this->create(EateryArea::class, ['area' => 'Primrose Hill', 'town_id' => $camden->id]);
        $this->build(Eatery::class)->createQuietly(['town_id' => $camden->id, 'county_id' => $county->id]);

        $result = json_decode((string) (new SearchArea())->handle(new Request([
            'town' => 'City of Westminster',
            'area' => '',
        ])), true);

        $this->assertContains('Soho', $result);
        $this->assertNotContains('Primrose Hill', $result);
    }

    #[Test]
    public function itReturnsEmptyWhenNoneMatch(): void
    {
        $county = $this->create(EateryCounty::class, ['country_id' => 1]);
        $town = $this->create(EateryTown::class, ['town' => 'City of Westminster', 'county_id' => $county->id]);
        $this->create(EateryArea::class, ['area' => 'Soho', 'town_id' => $town->id]);
        $this->build(Eatery::class)->createQuietly(['town_id' => $town->id, 'county_id' => $county->id]);

        $result = json_decode((string) (new SearchArea())->handle(new Request([
            'town' => 'City of Westminster',
            'area' => 'Nonexistent',
        ])), true);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itHasTheExpectedSchema(): void
    {
        $schema = (new SearchArea())->schema(new JsonSchemaTypeFactory());

        $this->assertArrayHasKey('borough', $schema);
        $this->assertArrayHasKey('area', $schema);
    }
}
