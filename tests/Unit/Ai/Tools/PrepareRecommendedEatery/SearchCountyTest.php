<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\PrepareRecommendedEatery;

use App\Ai\Tools\PrepareRecommendedEatery\SearchCounty;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SearchCountyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
    }

    #[Test]
    public function itReturnsCountiesMatchingTheSearch(): void
    {
        $country = $this->create(EateryCountry::class, ['country' => 'England']);
        $this->create(EateryCounty::class, ['county' => 'Dorset', 'country_id' => $country->id]);

        $result = json_decode((string) (new SearchCounty())->handle(new Request([
            'country' => 'England',
            'county' => 'Dorset',
        ])), true);

        $this->assertContains('Dorset', $result);
    }

    #[Test]
    public function itDoesAPartialMatch(): void
    {
        $country = $this->create(EateryCountry::class, ['country' => 'England']);
        $this->create(EateryCounty::class, ['county' => 'Dorset', 'country_id' => $country->id]);

        $result = json_decode((string) (new SearchCounty())->handle(new Request([
            'country' => 'England',
            'county' => 'Dor',
        ])), true);

        $this->assertContains('Dorset', $result);
    }

    #[Test]
    public function itOnlyReturnsCountiesForTheGivenCountry(): void
    {
        $england = $this->create(EateryCountry::class, ['country' => 'England']);
        $scotland = $this->create(EateryCountry::class, ['country' => 'Scotland']);

        $this->create(EateryCounty::class, ['county' => 'Dorset', 'country_id' => $england->id]);
        $this->create(EateryCounty::class, ['county' => 'Perthshire', 'country_id' => $scotland->id]);

        $result = json_decode((string) (new SearchCounty())->handle(new Request([
            'country' => 'England',
            'county' => '',
        ])), true);

        $this->assertContains('Dorset', $result);
        $this->assertNotContains('Perthshire', $result);
    }

    #[Test]
    public function itReturnsEmptyWhenNoneMatch(): void
    {
        $country = $this->create(EateryCountry::class, ['country' => 'England']);
        $this->create(EateryCounty::class, ['county' => 'Dorset', 'country_id' => $country->id]);

        $result = json_decode((string) (new SearchCounty())->handle(new Request([
            'country' => 'England',
            'county' => 'Nonexistent',
        ])), true);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itHasTheExpectedSchema(): void
    {
        $schema = (new SearchCounty())->schema(new JsonSchemaTypeFactory());

        $this->assertArrayHasKey('country', $schema);
        $this->assertArrayHasKey('county', $schema);
    }
}
