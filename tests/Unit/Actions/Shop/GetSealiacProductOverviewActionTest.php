<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\GetSealiacProductOverviewAction;
use App\Models\SealiacOverview;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use App\Support\Ai\Prompts\ShopProductSealiacOverviewPrompt;
use Exception;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Resources\Chat;
use OpenAI\Responses\Chat\CreateResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetSealiacProductOverviewActionTest extends TestCase
{
    protected ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = $this->create(ShopProduct::class);

        OpenAI::fake();
    }

    #[Test]
    public function itWillReturnTheLatestSealiacOverviewForAProductIfOneExists(): void
    {
        $model = $this->build(SealiacOverview::class)->forProduct($this->product)->create([
            'overview' => 'This is the overview',
        ]);

        $overview = app(GetSealiacProductOverviewAction::class)->handle($this->product);

        $this->assertTrue($model->is($overview));

        OpenAI::assertNothingSent();
    }


    #[Test]
    public function itWillThrowAnErrorIfNoReviewsExistWhenThereIsNoExistingRecordInTheDatabase(): void
    {
        ShopOrderReviewItem::truncate();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('No reviews found to generate overview');

        app(GetSealiacProductOverviewAction::class)->handle($this->product);
    }

    #[Test]
    public function itWillGetANewOverviewForAProductFromOpenAiUsingTheCorrectPrompt(): void
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

        $this->build(ShopOrderReviewItem::class)->forProduct($this->product)->createQuietly();

        $this->mock(ShopProductSealiacOverviewPrompt::class)
            ->shouldReceive('handle')
            ->withArgs(function ($argProduct) {
                $this->assertTrue($this->product->is($argProduct));

                return true;
            })
            ->andReturn('This is the prompt')
            ->once();

        app(GetSealiacProductOverviewAction::class)->handle($this->product);

        OpenAI::assertSent(Chat::class, function (string $method, array $parameters): bool {
            $this->assertEquals('create', $method);

            $this->assertArrayHasKey('model', $parameters);
            $this->assertEquals('gpt-3.5-turbo-1106', $parameters['model']);

            return true;
        });
    }

    #[Test]
    public function itWillStoreTheReturnedOverviewAgainstTheProduct(): void
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

        $this->build(ShopOrderReviewItem::class)->forProduct($this->product)->createQuietly();

        $this->mock(ShopProductSealiacOverviewPrompt::class)
            ->shouldReceive('handle')
            ->andReturn('This is the prompt')
            ->once();

        app(GetSealiacProductOverviewAction::class)->handle($this->product);

        $this->assertDatabaseCount(SealiacOverview::class, 1);

        $this->product->refresh();

        $this->assertNotNull($this->product->sealiacOverview);
        $this->assertCount(1, $this->product->sealiacOverviews);

        $this->assertEquals('This is the overview', $this->product->sealiacOverview->overview);
    }
}
