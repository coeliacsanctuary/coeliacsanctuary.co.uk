<?php

declare(strict_types=1);

namespace Tests\Feature\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Resources\EatingOut\CountyEateryResource;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CountyEateryResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    #[Test]
    public function itReturnsTheSnippetAsTheInfo(): void
    {
        $eatery = $this->create(Eatery::class, [
            'snippet' => 'A short and snappy summary',
            'info' => 'Something much longer',
        ]);

        $resource = (new CountyEateryResource($eatery))->toArray(request());

        $this->assertArrayHasKey('info', $resource);
        $this->assertEquals('A short and snappy summary', $resource['info']);
    }

    #[Test]
    public function itFallsBackToATruncatedVersionOfTheInfoWhenTheresNoSnippet(): void
    {
        $info = Str::repeat('word ', 100);

        $eatery = $this->create(Eatery::class, ['snippet' => null, 'info' => $info]);

        $resource = (new CountyEateryResource($eatery))->toArray(request());

        $this->assertEquals(Str::limit($info, 125, preserveWords: true), $resource['info']);
    }

    #[Test]
    public function itFallsBackToTheFirstAttractionRestaurantsInfoForAnAttraction(): void
    {
        $eatery = $this->build(Eatery::class)->attraction()->create(['snippet' => null, 'info' => null]);

        $this->build(EateryAttractionRestaurant::class)
            ->on($eatery)
            ->create(['info' => 'The first restaurant in the attraction']);

        $eatery->load('restaurants');

        $resource = (new CountyEateryResource($eatery))->toArray(request());

        $this->assertEquals('The first restaurant in the attraction', $resource['info']);
    }
}
