<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\GetSealiacEateryOverviewAction;
use App\Ai\Agents\SealiacEateryOverviewAgent;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use App\Models\SealiacOverview;
use Database\Seeders\EateryScaffoldingSeeder;
use Exception;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetSealiacEateryOverviewActionTest extends TestCase
{
    protected Eatery $eatery;

    protected NationwideBranch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);

        $this->branch = $this->build(NationwideBranch::class)->forEatery($this->eatery)->create();

        SealiacEateryOverviewAgent::fake();
    }

    #[Test]
    public function itWillReturnTheLatestSealiacOverviewForAnEateryIfOneExists(): void
    {
        $model = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create([
            'overview' => 'This is the overview',
        ]);

        $overview = app(GetSealiacEateryOverviewAction::class)->handle($this->eatery);

        $this->assertTrue($model->is($overview));

        SealiacEateryOverviewAgent::assertNeverPrompted();
    }

    #[Test]
    public function itWillReturnTheLatestSealiacOverviewForANationwideBranchIfItExists(): void
    {
        $model = $this->build(SealiacOverview::class)->forNationwideBranch($this->branch)->create([
            'overview' => 'This is the branch overview',
        ]);

        $overview = app(GetSealiacEateryOverviewAction::class)->handle($this->eatery, $this->branch);

        $this->assertTrue($model->is($overview));

        SealiacEateryOverviewAgent::assertNeverPrompted();
    }

    #[Test]
    public function itWillNotReturnTheOverviewForTheEateryWhenGettingTheBranchOverview(): void
    {
        $eateryModel = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create([
            'overview' => 'This is the eatery overview',
        ]);

        $branchModel = $this->build(SealiacOverview::class)->forNationwideBranch($this->branch)->create([
            'overview' => 'This is the branch overview',
        ]);

        $overview = app(GetSealiacEateryOverviewAction::class)->handle($this->eatery, $this->branch);

        $this->assertTrue($branchModel->is($overview));

        SealiacEateryOverviewAgent::assertNeverPrompted();
    }

    #[Test]
    public function itWillThrowAnErrorIfNoReviewsExistWhenThereIsNoExistingRecordInTheDatabase(): void
    {
        EateryReview::truncate();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No reviews found to generate overview');

        app(GetSealiacEateryOverviewAction::class)->handle($this->eatery);
    }

    #[Test]
    public function itWillPromptTheSealiacEateryOverviewAgentForAnEatery(): void
    {
        SealiacEateryOverviewAgent::fake(['foo']);

        $this->build(EateryReview::class)->createQuietly([
            'wheretoeat_id' => $this->eatery->id,
            'nationwide_branch_id' => $this->branch->id,
            'approved' => true,
        ]);

        app(GetSealiacEateryOverviewAction::class)->handle($this->eatery);

        SealiacEateryOverviewAgent::assertPrompted('Generate your overview.');
    }

    #[Test]
    public function itWillPromptTheSealiacEateryOverviewAgentForANationwideBranch(): void
    {
        SealiacEateryOverviewAgent::fake(['foo']);

        $this->build(EateryReview::class)->createQuietly([
            'wheretoeat_id' => $this->branch->wheretoeat_id,
            'nationwide_branch_id' => $this->branch->id,
            'approved' => true,
        ]);

        app(GetSealiacEateryOverviewAction::class)->handle($this->eatery, $this->branch);

        SealiacEateryOverviewAgent::assertPrompted('Generate your overview.');
    }

    #[Test]
    public function itWillStoreTheReturnedOverviewAgainstTheEatery(): void
    {
        $this->assertDatabaseEmpty(SealiacOverview::class);

        SealiacEateryOverviewAgent::fake(['This is the overview']);

        $this->build(EateryReview::class)->createQuietly([
            'wheretoeat_id' => $this->eatery->id,
            'nationwide_branch_id' => $this->branch->id,
            'approved' => true,
        ]);

        app(GetSealiacEateryOverviewAction::class)->handle($this->eatery);

        $this->assertDatabaseCount(SealiacOverview::class, 1);

        $this->eatery->refresh();

        $this->assertNotNull($this->eatery->sealiacOverview);
        $this->assertCount(1, $this->eatery->sealiacOverviews);

        $this->assertEquals('This is the overview', $this->eatery->sealiacOverview->overview);
    }

    #[Test]
    public function itWillStoreTheReturnedOverviewAgainstTheNationwideBranch(): void
    {
        $this->assertDatabaseEmpty(SealiacOverview::class);

        SealiacEateryOverviewAgent::fake(['This is the overview']);

        $this->build(EateryReview::class)->createQuietly([
            'wheretoeat_id' => $this->branch->wheretoeat_id,
            'nationwide_branch_id' => $this->branch->id,
            'approved' => true,
        ]);

        app(GetSealiacEateryOverviewAction::class)->handle($this->eatery, $this->branch);

        $this->assertDatabaseCount(SealiacOverview::class, 1);

        $this->eatery->refresh();
        $this->branch->refresh();

        $this->assertNull($this->eatery->sealiacOverview);
        $this->assertEmpty($this->eatery->sealiacOverviews);

        $this->assertNotNull($this->branch->sealiacOverview);
        $this->assertCount(1, $this->branch->sealiacOverviews);

        $this->assertEquals('This is the overview', $this->branch->sealiacOverview->overview);
    }
}
