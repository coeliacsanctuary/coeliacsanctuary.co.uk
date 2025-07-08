<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\EatingOut\SealiacOverview;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\EatingOut\SealiacOverview;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected Eatery $eatery;

    protected NationwideBranch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eatery = $this->create(Eatery::class);
        $this->branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->eatery->id,
        ]);
    }

    #[Test]
    public function itReturnsNotFoundIfTheEateryDoesntExist(): void
    {
        $this->postJson(route('api.wheretoeat.sealiac.rating.store', ['eatery' => 123]))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheEateryIsntLive(): void
    {
        $this->eatery->update(['live' => false]);

        $this->postJson(route('api.wheretoeat.sealiac.rating.store', $this->eatery))->assertNotFound();
    }

    #[Test]
    public function itReturnsAValidationErrorIfTheRatingPropertyIsntAnExpectedValue(): void
    {
        $this->postJson(route('api.wheretoeat.sealiac.rating.store', $this->eatery), [
            'rating' => 'foo',
        ])->assertJsonValidationErrorFor('rating');
    }

    #[Test]
    #[DataProvider('ratings')]
    public function itUpdatesTheRatingForTheGivenEatery(string $rating, string $column): void
    {
        $overview = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();

        $this->assertEquals(0, $overview->$column);

        $this->postJson(route('api.wheretoeat.sealiac.rating.store', $this->eatery), [
            'rating' => $rating,
        ])->assertNoContent();

        $this->assertEquals(1, $overview->refresh()->$column);
    }

    #[Test]
    #[DataProvider('ratings')]
    public function itOnlyUpdatesTheLatestActiveRatingForTheGivenEatery(string $rating, string $column): void
    {
        $oldOverview = $this->build(SealiacOverview::class)
            ->forEatery($this->eatery)
            ->invalidated()
            ->create([
                $column => 2,
            ]);

        $overview = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();

        $this->assertEquals(2, $oldOverview->$column);
        $this->assertEquals(0, $overview->$column);

        $this->postJson(route('api.wheretoeat.sealiac.rating.store', $this->eatery), [
            'rating' => $rating,
        ])->assertNoContent();

        $this->assertEquals(2, $oldOverview->refresh()->$column);
        $this->assertEquals(1, $overview->refresh()->$column);
    }

    #[Test]
    #[DataProvider('ratings')]
    public function ifAnOverviewExistsForTheEateryAndChildBranchItOnlyUpdatesTheParentWhenBranchIsNull(string $rating, string $column): void
    {
        $branchOverview = $this->build(SealiacOverview::class)
            ->forEatery($this->eatery)
            ->forNationwideBranch($this->branch)
            ->create([
                $column => 2,
            ]);

        $parentOverview = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();

        $this->assertEquals(2, $branchOverview->$column);
        $this->assertEquals(0, $parentOverview->$column);

        $this->postJson(route('api.wheretoeat.sealiac.rating.store', $this->eatery), [
            'rating' => $rating,
        ])->assertNoContent();

        $this->assertEquals(2, $branchOverview->refresh()->$column);
        $this->assertEquals(1, $parentOverview->refresh()->$column);
    }

    #[Test]
    #[DataProvider('ratings')]
    public function itUpdatesTheRatingForTheGivenBranch(string $rating, string $column): void
    {
        $overview = $this->build(SealiacOverview::class)->forNationwideBranch($this->branch)->create();

        $this->assertEquals(0, $overview->$column);

        $this->postJson(route('api.wheretoeat.sealiac.rating.store', ['eatery' => $this->eatery, 'branchId' => $this->branch->id]), [
            'rating' => $rating,
        ])->assertNoContent();

        $this->assertEquals(1, $overview->refresh()->$column);
    }

    #[Test]
    #[DataProvider('ratings')]
    public function ifAnOverviewExistsForTheEateryAndChildBranchItOnlyUpdatesTheBranchWhenBranchIsSet(string $rating, string $column): void
    {
        $branchOverview = $this->build(SealiacOverview::class)
            ->forEatery($this->eatery)
            ->forNationwideBranch($this->branch)
            ->create();

        $parentOverview = $this->build(SealiacOverview::class)
            ->forEatery($this->eatery)
            ->create([
                $column => 2,
            ]);

        $this->assertEquals(2, $parentOverview->$column);
        $this->assertEquals(0, $branchOverview->$column);

        $this->postJson(route('api.wheretoeat.sealiac.rating.store', ['eatery' => $this->eatery, 'branchId' => $this->branch->id]), [
            'rating' => $rating,
        ])->assertNoContent();

        $this->assertEquals(2, $parentOverview->refresh()->$column);
        $this->assertEquals(1, $branchOverview->refresh()->$column);
    }

    public static function ratings(): array
    {
        return [
            'up' => ['up', 'thumbs_up'],
            'down' => ['down', 'thumbs_down'],
        ];
    }
}
