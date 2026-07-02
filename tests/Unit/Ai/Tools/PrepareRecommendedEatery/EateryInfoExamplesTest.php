<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\PrepareRecommendedEatery;

use App\Ai\Tools\PrepareRecommendedEatery\EateryInfoExamples;
use App\Models\EatingOut\Eatery;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryInfoExamplesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itReturnsInfoFromRecentEateries(): void
    {
        $eatery = $this->build(Eatery::class)->createQuietly([
            'info' => 'A lovely gluten free cafe.',
            'country_id' => 2,
            'created_at' => now(),
        ]);

        $result = json_decode((string) (new EateryInfoExamples())->handle(new Request()), true);

        $this->assertContains($eatery->info, $result);
    }

    #[Test]
    public function itExcludesEateriesOlderThanSixMonths(): void
    {
        $this->build(Eatery::class)->createQuietly([
            'info' => 'An old cafe.',
            'country_id' => 2,
            'created_at' => now()->subMonths(7),
        ]);

        $result = json_decode((string) (new EateryInfoExamples())->handle(new Request()), true);

        $this->assertNotContains('An old cafe.', $result);
    }

    #[Test]
    public function itExcludesNationwideEateries(): void
    {
        $this->build(Eatery::class)->createQuietly([
            'info' => 'A nationwide chain.',
            'country_id' => 1,
            'created_at' => now(),
        ]);

        $result = json_decode((string) (new EateryInfoExamples())->handle(new Request()), true);

        $this->assertNotContains('A nationwide chain.', $result);
    }

    #[Test]
    public function itReturnsUpToTenResults(): void
    {
        $this->build(Eatery::class)->count(15)->createQuietly([
            'country_id' => 2,
            'created_at' => now(),
        ]);

        $result = json_decode((string) (new EateryInfoExamples())->handle(new Request()), true);

        $this->assertLessThanOrEqual(10, count($result));
    }

    #[Test]
    public function itHasAnEmptySchema(): void
    {
        $this->assertEmpty((new EateryInfoExamples())->schema(new JsonSchemaTypeFactory()));
    }
}
