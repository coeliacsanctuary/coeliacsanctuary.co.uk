<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\PrepareRecommendedEatery;

use App\Ai\Tools\PrepareRecommendedEatery\EateryCuisineList;
use App\Models\EatingOut\EateryCuisine;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryCuisineListTest extends TestCase
{
    #[Test]
    public function itReturnsAllCuisinesOrderedAlphabetically(): void
    {
        $this->create(EateryCuisine::class, ['cuisine' => 'Italian']);
        $this->create(EateryCuisine::class, ['cuisine' => 'Chinese']);
        $this->create(EateryCuisine::class, ['cuisine' => 'English']);

        $result = json_decode((string) (new EateryCuisineList())->handle(new Request()), true);

        $this->assertEquals(['Chinese', 'English', 'Italian'], $result);
    }

    #[Test]
    public function itReturnsEmptyWhenNoCuisinesExist(): void
    {
        $result = json_decode((string) (new EateryCuisineList())->handle(new Request()), true);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itHasAnEmptySchema(): void
    {
        $this->assertEmpty((new EateryCuisineList())->schema(new JsonSchemaTypeFactory()));
    }
}
