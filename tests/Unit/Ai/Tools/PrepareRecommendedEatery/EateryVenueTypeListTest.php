<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\PrepareRecommendedEatery;

use App\Ai\Tools\PrepareRecommendedEatery\EateryVenueTypeList;
use App\Models\EatingOut\EateryVenueType;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryVenueTypeListTest extends TestCase
{
    #[Test]
    public function itReturnsAllVenueTypesOrderedAlphabetically(): void
    {
        $this->create(EateryVenueType::class, ['venue_type' => 'Restaurant']);
        $this->create(EateryVenueType::class, ['venue_type' => 'Bakery']);
        $this->create(EateryVenueType::class, ['venue_type' => 'Pub']);

        $result = json_decode((string) (new EateryVenueTypeList())->handle(new Request()), true);

        $this->assertEquals(['Bakery', 'Pub', 'Restaurant'], $result);
    }

    #[Test]
    public function itReturnsEmptyWhenNoVenueTypesExist(): void
    {
        $result = json_decode((string) (new EateryVenueTypeList())->handle(new Request()), true);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itHasAnEmptySchema(): void
    {
        $this->assertEmpty((new EateryVenueTypeList())->schema(new JsonSchemaTypeFactory()));
    }
}
