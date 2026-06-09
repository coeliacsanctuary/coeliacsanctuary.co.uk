<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\GetSealiacProductOverviewAction;
use App\Ai\Agents\SealiacProductOverviewAgent;
use App\Models\SealiacOverview;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use Exception;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetSealiacProductOverviewActionTest extends TestCase
{
    protected ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = $this->create(ShopProduct::class);

        SealiacProductOverviewAgent::fake();
    }

    #[Test]
    public function itWillReturnTheLatestSealiacOverviewForAProductIfOneExists(): void
    {
        $model = $this->build(SealiacOverview::class)->forProduct($this->product)->create([
            'overview' => 'This is the overview',
        ]);

        $overview = app(GetSealiacProductOverviewAction::class)->handle($this->product);

        $this->assertTrue($model->is($overview));

        SealiacProductOverviewAgent::assertNeverPrompted();
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
    public function itWillPromptTheSealiacProductOverviewAgent(): void
    {
        SealiacProductOverviewAgent::fake(['foo']);

        $this->build(ShopOrderReviewItem::class)->forProduct($this->product)->createQuietly();

        app(GetSealiacProductOverviewAction::class)->handle($this->product);

        SealiacProductOverviewAgent::assertPrompted('Generate your overview.');
    }

    #[Test]
    public function itWillStoreTheReturnedOverviewAgainstTheProduct(): void
    {
        $this->assertDatabaseEmpty(SealiacOverview::class);

        SealiacProductOverviewAgent::fake(['This is the overview']);

        $this->build(ShopOrderReviewItem::class)->forProduct($this->product)->createQuietly();

        app(GetSealiacProductOverviewAction::class)->handle($this->product);

        $this->assertDatabaseCount(SealiacOverview::class, 1);

        $this->product->refresh();

        $this->assertNotNull($this->product->sealiacOverview);
        $this->assertCount(1, $this->product->sealiacOverviews);

        $this->assertEquals('This is the overview', $this->product->sealiacOverview->overview);
    }
}
