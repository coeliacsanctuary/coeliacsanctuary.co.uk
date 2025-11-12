<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Eatery\SealiacOverview\Feedback;

use App\Models\EatingOut\Eatery;
use App\Models\SealiacOverview;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected Eatery $eatery;

    protected SealiacOverview $sealiacOverview;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
        $this->sealiacOverview = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();
    }

    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->postJson(route('api.v1.eating-out.details.sealiac-overview.feedback', $this->eatery))->assertForbidden();
    }

    #[Test]
    public function itErrorsWithoutARating(): void
    {
        $this->makeRequest(null)->assertJsonValidationErrorFor('rating');
        $this->makeRequest('foo')->assertJsonValidationErrorFor('rating');
    }

    #[Test]
    public function itReturnsNotFoundIfThereIsNoCoeliacOverview(): void
    {
        $this->sealiacOverview->delete();

        $this->makeRequest()->assertNotFound();
    }

    #[Test]
    public function itIncrementsTheThumbsUpCount(): void
    {
        $this->assertEquals(0, $this->sealiacOverview->thumbs_up);

        $this->makeRequest()->assertNoContent();

        $this->assertEquals(1, $this->sealiacOverview->refresh()->thumbs_up);
    }

    #[Test]
    public function itIncrementsTheThumbsDownCount(): void
    {
        $this->assertEquals(0, $this->sealiacOverview->thumbs_down);

        $this->makeRequest('down')->assertNoContent();

        $this->assertEquals(1, $this->sealiacOverview->refresh()->thumbs_down);
    }

    #[Test]
    public function itReturnsNoContent(): void
    {
        $this->makeRequest()->assertNoContent();
    }

    /** @param 'up'|'down'|null  $rating */
    protected function makeRequest(?string $rating = 'up', string $source = 'foo'): TestResponse
    {
        return $this->postJson(
            route('api.v1.eating-out.details.sealiac-overview.feedback', $this->eatery),
            ['rating' => $rating],
            ['x-coeliac-source' => $source],
        );
    }
}
