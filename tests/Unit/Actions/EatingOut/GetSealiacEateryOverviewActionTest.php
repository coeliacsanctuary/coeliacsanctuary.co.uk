<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\GetSealiacEateryOverviewAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use App\Models\SealiacOverview;
use App\Support\Ai\Prompts\EatingOutSealiacOverviewPrompt;
use Exception;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Resources\Chat;
use OpenAI\Responses\Chat\CreateResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetSealiacEateryOverviewActionTest extends TestCase
{
    protected Eatery $eatery;

    protected NationwideBranch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eatery = $this->create(Eatery::class);

        $this->branch = $this->build(NationwideBranch::class)->forEatery($this->eatery)->create();

        OpenAI::fake();
    }

    #[Test]
    public function itWillReturnTheLatestSealiacOverviewForAnEateryIfOneExists(): void
    {
        $this->build(SealiacOverview::class)->forEatery($this->eatery)->create([
            'overview' => 'This is the overview',
        ]);

        $overview = app(GetSealiacEateryOverviewAction::class)->handle($this->eatery);

        $this->assertEquals('This is the overview', $overview);

        OpenAI::assertNothingSent();
    }

    #[Test]
    public function itWillReturnTheLatestSealiacOverviewForANationwideBranchIfItExists(): void
    {
        $this->build(SealiacOverview::class)->forNationwideBranch($this->branch)->create([
            'overview' => 'This is the branch overview',
        ]);

        $overview = app(GetSealiacEateryOverviewAction::class)->handle($this->eatery, $this->branch);

        $this->assertEquals('This is the branch overview', $overview);

        OpenAI::assertNothingSent();
    }

    #[Test]
    public function itWillNotReturnTheOverviewForTheEateryWhenGettingTheBranchOverview(): void
    {
        $this->build(SealiacOverview::class)->forEatery($this->eatery)->create([
            'overview' => 'This is the eatery overview',
        ]);

        $this->build(SealiacOverview::class)->forNationwideBranch($this->branch)->create([
            'overview' => 'This is the branch overview',
        ]);

        $overview = app(GetSealiacEateryOverviewAction::class)->handle($this->eatery, $this->branch);

        $this->assertEquals('This is the branch overview', $overview);

        OpenAI::assertNothingSent();
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
    public function itWillGetANewOverviewForAnEateryFromOpenAiUsingTheCorrectPrompt(): void
    {
        OpenAI::fake([CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => 'foo',
                    ],
                ],
            ],
        ])]);

        $this->build(EateryReview::class)->createQuietly([
            'wheretoeat_id' => $this->eatery->id,
            'nationwide_branch_id' => $this->branch->id,
            'approved' => true,
        ]);

        $this->mock(EatingOutSealiacOverviewPrompt::class)
            ->shouldReceive('handle')
            ->withArgs(function ($argEatery, $argBranch) {
                $this->assertTrue($this->eatery->is($argEatery));
                $this->assertNull($argBranch);

                return true;
            })
            ->andReturn('This is the prompt')
            ->once();

        app(GetSealiacEateryOverviewAction::class)->handle($this->eatery);

        OpenAI::assertSent(Chat::class, function (string $method, array $parameters): bool {
            $this->assertEquals('create', $method);

            $this->assertArrayHasKey('model', $parameters);
            $this->assertEquals('gpt-3.5-turbo-1106', $parameters['model']);

            return true;
        });
    }

    #[Test]
    public function itWillGetANewOverviewForANationwideBranchFromOpenAiUsingTheCorrectPrompt(): void
    {
        OpenAI::fake([CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => 'foo',
                    ],
                ],
            ],
        ])]);

        $this->build(EateryReview::class)->createQuietly([
            'wheretoeat_id' => $this->branch->wheretoeat_id,
            'nationwide_branch_id' => $this->branch->id,
            'approved' => true,
        ]);

        $this->mock(EatingOutSealiacOverviewPrompt::class)
            ->shouldReceive('handle')
            ->withArgs(function ($argEatery, $argBranch) {
                $this->assertTrue($this->eatery->is($argEatery));
                $this->assertTrue($this->branch->is($argBranch));

                return true;
            })
            ->andReturn('This is the prompt')
            ->once();

        app(GetSealiacEateryOverviewAction::class)->handle($this->eatery, $this->branch);

        OpenAI::assertSent(Chat::class, function (string $method, array $parameters): bool {
            $this->assertEquals('create', $method);

            $this->assertArrayHasKey('model', $parameters);
            $this->assertEquals('gpt-3.5-turbo-1106', $parameters['model']);

            return true;
        });
    }

    #[Test]
    public function itWillStoreTheReturnedOverviewAgainstTheEatery(): void
    {
        $this->assertDatabaseEmpty(SealiacOverview::class);

        OpenAI::fake([CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => 'This is the overview',
                    ],
                ],
            ],
        ])]);

        $this->build(EateryReview::class)->createQuietly([
            'wheretoeat_id' => $this->eatery->id,
            'nationwide_branch_id' => $this->branch->id,
            'approved' => true,
        ]);

        $this->mock(EatingOutSealiacOverviewPrompt::class)
            ->shouldReceive('handle')
            ->andReturn('This is the prompt')
            ->once();

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

        OpenAI::fake([CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => 'This is the overview',
                    ],
                ],
            ],
        ])]);

        $this->build(EateryReview::class)->createQuietly([
            'wheretoeat_id' => $this->branch->wheretoeat_id,
            'nationwide_branch_id' => $this->branch->id,
            'approved' => true,
        ]);

        $this->mock(EatingOutSealiacOverviewPrompt::class)
            ->shouldReceive('handle')
            ->andReturn('This is the prompt')
            ->once();

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
