<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\SealiacOverviewFeedback;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\SealiacOverview;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected SealiacOverview $sealiacOverview;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sealiacOverview = $this->create(SealiacOverview::class);
    }

    #[Test]
    public function itReturnsNotFoundIfTheOverviewDoesntExist(): void
    {
        $this->postJson(route('api.sealiac-overview-feedback', ['sealiacOverview' => 123]))->assertNotFound();
    }

    #[Test]
    public function itReturnsAValidationErrorIfTheRatingPropertyIsntAnExpectedValue(): void
    {
        $this->postJson(route('api.sealiac-overview-feedback', $this->sealiacOverview), [
            'rating' => 'foo',
        ])->assertJsonValidationErrorFor('rating');
    }

    #[Test]
    #[DataProvider('ratings')]
    public function itUpdatesTheRatingForTheGivenOverview(string $rating, string $column): void
    {
        $this->assertEquals(0, $this->sealiacOverview->$column);

        $this->postJson(route('api.sealiac-overview-feedback', $this->sealiacOverview), [
            'rating' => $rating,
        ])->assertNoContent();

        $this->assertEquals(1, $this->sealiacOverview->refresh()->$column);
    }

    #[Test]
    #[DataProvider('ratings')]
    public function itOnlyUpdatesTheLatestActiveRatingForTheGivenSealiacOverview(string $rating, string $column): void
    {
        $oldOverview = $this->build(SealiacOverview::class)
            ->invalidated()
            ->create([
                $column => 2,
            ]);

        $this->assertEquals(2, $oldOverview->$column);
        $this->assertEquals(0, $this->sealiacOverview->$column);

        $this->postJson(route('api.sealiac-overview-feedback', $this->sealiacOverview), [
            'rating' => $rating,
        ])->assertNoContent();

        $this->assertEquals(2, $oldOverview->refresh()->$column);
        $this->assertEquals(1, $this->sealiacOverview->refresh()->$column);
    }

    public static function ratings(): array
    {
        return [
            'up' => ['up', 'thumbs_up'],
            'down' => ['down', 'thumbs_down'],
        ];
    }
}
