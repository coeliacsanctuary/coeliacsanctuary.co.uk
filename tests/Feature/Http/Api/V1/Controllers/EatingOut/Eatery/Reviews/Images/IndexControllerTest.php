<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\Images;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReviewImage;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
        $this->build(EateryReviewImage::class)->on($this->eatery)->count(5)->create();
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.details.reviews.images.index', $this->eatery))->assertForbidden();
    }

    #[Test]
    public function itReturnsADataKey(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => []]);
    }

    #[Test]
    public function itReturnsAllImages(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonCount(5, 'data');
    }

    #[Test]
    public function itReturnsTheExpectedPayload(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => [
                ['id', 'thumbnail', 'path'],
            ]]);
    }

    protected function makeRequest(array $params = [], string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.details.reviews.images.index', ['eatery' => $this->eatery, ...$params]),
            ['x-coeliac-source' => $source],
        );
    }
}
