<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
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

        $this->build(EateryReview::class)->count(5)->approved()->on($this->eatery)->create();
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.details.reviews.index', $this->eatery))->assertForbidden();
    }

    #[Test]
    public function itReturnsADataKey(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => ['average', 'total', 'reviews' => []]]);
    }

    #[Test]
    public function itReturnsAllReviews(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonCount(5, 'data.reviews');
    }

    #[Test]
    public function itDoesntReturnNonApprovedReviews(): void
    {
        $this->eatery->reviews()->inRandomOrder()->first()->update(['approved' => false]);

        $this->makeRequest()
            ->assertOk()
            ->assertJsonCount(4, 'data.reviews');
    }

    #[Test]
    public function itReturnsTheExpectedData(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => ['reviews' => [[
                'name',
                'published',
                'date_diff',
                'body',
                'rating',
                'expense',
                'food_rating',
                'service_rating',
                'branch_name',
                'images',
            ]]]]);
    }

    protected function makeRequest(array $params = [], string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.details.reviews.index', ['eatery' => $this->eatery, ...$params]),
            ['x-coeliac-source' => $source],
        );
    }
}
