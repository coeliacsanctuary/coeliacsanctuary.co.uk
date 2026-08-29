<?php

declare(strict_types=1);

namespace Tests\Feature\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Resources\EatingOut\EateryListResource;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryListResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    protected function makeEatery(array $attributes = []): Eatery
    {
        $eatery = $this->create(Eatery::class, $attributes);

        $eatery->load(['restaurants', 'features', 'reviews', 'venueType', 'type', 'cuisine']);

        return $eatery;
    }

    #[Test]
    public function itReturnsTheSnippetAsTheInfo(): void
    {
        $eatery = $this->makeEatery([
            'snippet' => 'A short and snappy summary',
            'info' => 'Something much longer',
        ]);

        $resource = (new EateryListResource($eatery))->toArray(request());

        $this->assertArrayHasKey('info', $resource);
        $this->assertEquals('A short and snappy summary', $resource['info']);
    }

    #[Test]
    public function itFallsBackToATruncatedVersionOfTheInfoWhenTheresNoSnippet(): void
    {
        $info = Str::repeat('word ', 100);

        $eatery = $this->makeEatery(['snippet' => null, 'info' => $info]);

        $resource = (new EateryListResource($eatery))->toArray(request());

        $this->assertEquals(Str::limit($info, 125, preserveWords: true), $resource['info']);
    }

    #[Test]
    public function itFallsBackToTheFirstAttractionRestaurantsInfoForAnAttraction(): void
    {
        $eatery = $this->build(Eatery::class)->attraction()->create(['snippet' => null, 'info' => null]);

        $this->build(EateryAttractionRestaurant::class)
            ->on($eatery)
            ->create(['info' => 'The first restaurant in the attraction']);

        $eatery->load(['restaurants', 'features', 'reviews', 'venueType', 'type', 'cuisine']);

        $resource = (new EateryListResource($eatery))->toArray(request());

        $this->assertEquals('The first restaurant in the attraction', $resource['info']);
    }
}
