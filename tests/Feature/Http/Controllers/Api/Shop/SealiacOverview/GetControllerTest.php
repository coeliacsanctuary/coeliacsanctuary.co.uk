<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Shop\SealiacOverview;

use App\Actions\SealiacOverview\FormatResponseAction;
use App\Actions\Shop\GetSealiacProductOverviewAction;
use App\Models\SealiacOverview;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Exception;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    protected ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = $this->create(ShopProduct::class);
        $this->build(ShopProductVariant::class)->belongsToProduct($this->product)->create();

        OpenAI::fake();
    }

    #[Test]
    public function itReturnsNotFoundIfTheProductDoesntExist(): void
    {
        $this->getJson(route('api.shop.products.sealiac.get', ['product' => 123]))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheProductDoesntHaveAnyVariants(): void
    {
        $this->product->variants()->delete();

        $this->getJson(route('api.shop.products.sealiac.get', $this->product))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundIfTheProductIsntLive(): void
    {
        $this->product->variants()->update(['live' => false]);

        $this->getJson(route('api.shop.products.sealiac.get', $this->product))->assertNotFound();
    }

    #[Test]
    public function itCallsTheGetSealiacShopProductOverviewActionWithTheProduct(): void
    {
        $this->mock(GetSealiacProductOverviewAction::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(function ($product) {
                $this->assertTrue($this->product->is($product));

                return true;
            })
            ->andReturn($this->create(SealiacOverview::class));

        $this->getJson(route('api.shop.products.sealiac.get', $this->product))->assertOk();
    }

    #[Test]
    public function itReturnsJsonWithADataAttribute(): void
    {
        $this->mock(GetSealiacProductOverviewAction::class)
            ->shouldReceive('handle')
            ->andReturn($this->create(SealiacOverview::class));

        $this->getJson(route('api.shop.products.sealiac.get', $this->product))->assertJsonStructure(['data' => ['overview', 'id']]);
    }

    #[Test]
    public function itCallsTheFormatResponseAction(): void
    {
        $overview = $this->create(SealiacOverview::class, ['overview' => 'this is the ai overview']);

        $this->mock(GetSealiacProductOverviewAction::class)
            ->shouldReceive('handle')
            ->andReturn($overview);

        $this->mock(FormatResponseAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($argResponse) {
                $this->assertEquals('this is the ai overview', $argResponse);

                return true;
            })
            ->once()
            ->andReturn(Str::of('foo'));

        $this->getJson(route('api.shop.products.sealiac.get', $this->product))->assertExactJson(['data' => [
            'overview' => Str::of('foo'),
            'id' => $overview->id,
        ]]);
    }

    #[Test]
    public function itCanHandleTheActionErroringAndReturnsAsNotFound(): void
    {
        $this->mock(GetSealiacProductOverviewAction::class)
            ->shouldReceive('handle')
            ->andThrow(new Exception());

        $this->getJson(route('api.shop.products.sealiac.get', $this->product))->assertNotFound();
    }
}
