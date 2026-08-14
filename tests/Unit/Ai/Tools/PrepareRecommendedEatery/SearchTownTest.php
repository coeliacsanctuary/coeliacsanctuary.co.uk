<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\PrepareRecommendedEatery;

use App\Ai\Tools\PrepareRecommendedEatery\SearchTown;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SearchTownTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itReturnsTownsMatchingTheSearch(): void
    {
        $county = $this->create(EateryCounty::class, ['county' => 'Dorset', 'country_id' => 1]);
        $town = $this->create(EateryTown::class, ['town' => 'Bournemouth', 'county_id' => $county->id]);
        $this->build(Eatery::class)->createQuietly(['town_id' => $town->id, 'county_id' => $county->id]);

        $result = json_decode((string) (new SearchTown())->handle(new Request([
            'county' => 'Dorset',
            'town' => 'Bournemouth',
        ])), true);

        $this->assertContains('Bournemouth', $result);
    }

    #[Test]
    public function itDoesAPartialMatch(): void
    {
        $county = $this->create(EateryCounty::class, ['county' => 'Dorset', 'country_id' => 1]);
        $town = $this->create(EateryTown::class, ['town' => 'Bournemouth', 'county_id' => $county->id]);
        $this->build(Eatery::class)->createQuietly(['town_id' => $town->id, 'county_id' => $county->id]);

        $result = json_decode((string) (new SearchTown())->handle(new Request([
            'county' => 'Dorset',
            'town' => 'Bourne',
        ])), true);

        $this->assertContains('Bournemouth', $result);
    }

    #[Test]
    public function itOnlyReturnsTownsForTheGivenCounty(): void
    {
        $dorset = $this->create(EateryCounty::class, ['county' => 'Dorset', 'country_id' => 1]);
        $bournemouth = $this->create(EateryTown::class, ['town' => 'Bournemouth', 'county_id' => $dorset->id]);
        $this->build(Eatery::class)->createQuietly(['town_id' => $bournemouth->id, 'county_id' => $dorset->id]);

        $devon = $this->create(EateryCounty::class, ['county' => 'Devon', 'country_id' => 1]);
        $exeter = $this->create(EateryTown::class, ['town' => 'Exeter', 'county_id' => $devon->id]);
        $this->build(Eatery::class)->createQuietly(['town_id' => $exeter->id, 'county_id' => $devon->id]);

        $result = json_decode((string) (new SearchTown())->handle(new Request([
            'county' => 'Dorset',
            'town' => '',
        ])), true);

        $this->assertContains('Bournemouth', $result);
        $this->assertNotContains('Exeter', $result);
    }

    #[Test]
    public function itReturnsEmptyWhenNoneMatch(): void
    {
        $county = $this->create(EateryCounty::class, ['county' => 'Dorset', 'country_id' => 1]);
        $town = $this->create(EateryTown::class, ['town' => 'Bournemouth', 'county_id' => $county->id]);
        $this->build(Eatery::class)->createQuietly(['town_id' => $town->id, 'county_id' => $county->id]);

        $result = json_decode((string) (new SearchTown())->handle(new Request([
            'county' => 'Dorset',
            'town' => 'Nonexistent',
        ])), true);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itHasTheExpectedSchema(): void
    {
        $schema = (new SearchTown())->schema(new JsonSchemaTypeFactory());

        $this->assertArrayHasKey('county', $schema);
        $this->assertArrayHasKey('town', $schema);
    }
}
