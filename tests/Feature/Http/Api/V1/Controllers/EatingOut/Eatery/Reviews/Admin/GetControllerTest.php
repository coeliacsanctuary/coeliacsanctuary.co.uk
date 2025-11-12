<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\Admin;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    protected Eatery $eatery;

    protected EateryReview $review;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
        $this->review = $this->build(EateryReview::class)->adminReview()->on($this->eatery)->create();
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.details.reviews.admin-review', $this->eatery))->assertForbidden();
    }

    #[Test]
    public function itReturnsNotFoundIfThereIsNoAdminReview(): void
    {
        $this->review->delete();

        $this->makeRequest()->assertNotFound();
    }

    #[Test]
    public function itReturnsADataKey(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => []]);
    }

    #[Test]
    public function itReturnsTheExpectedData(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertExactJson(['data' => [
                'name' => $this->review->name,
                'published' => $this->review->created_at,
                'date_diff' => $this->review->human_date,
                'body' => $this->review->review,
                'rating' => (float) $this->review->rating,
                'expense' => $this->review->price,
                'food_rating' => $this->review->food_rating,
                'service_rating' => $this->review->service_rating,
                'branch_name' => $this->review->branch_name,
                'images' => [],
            ]]);
    }

    protected function makeRequest(array $params = [], string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.details.reviews.admin-review', ['eatery' => $this->eatery, ...$params]),
            ['x-coeliac-source' => $source],
        );
    }
}
