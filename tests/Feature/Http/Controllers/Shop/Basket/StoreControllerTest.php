<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Shop\Basket;

use App\Models\Shop\ShopProductAddOn;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\Shop\AddProductToBasketAction;
use App\Actions\Shop\ResolveBasketAction;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Testing\TestResponse;
use Spatie\TestTime\TestTime;
use Tests\RequestFactories\ShopAddBasketRequestFactory;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts(1, 1);

        $this->product = ShopProduct::query()->first();
        $this->variant = $this->product->variants->first();
    }

    #[Test]
    public function itReturnsAValidationErrorWithAnInvalidProductId(): void
    {
        $this->makeRequest(['product_id' => null])->assertSessionHasErrors('product_id');
        $this->makeRequest(['product_id' => 'foo'])->assertSessionHasErrors('product_id');
        $this->makeRequest(['product_id' => true])->assertSessionHasErrors('product_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithAProductThatDoesntExist(): void
    {
        $this->makeRequest(['product_id' => 123])->assertSessionHasErrors('product_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithAProductThatHasNoLiveVariants(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->makeRequest(['product_id' => $product->id])->assertSessionHasErrors('product_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithAnInvalidVariantId(): void
    {
        $this->makeRequest(['variant_id' => null])->assertSessionHasErrors('variant_id');
        $this->makeRequest(['variant_id' => 'foo'])->assertSessionHasErrors('variant_id');
        $this->makeRequest(['variant_id' => true])->assertSessionHasErrors('variant_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithAVariantThatDoesntExist(): void
    {
        $this->makeRequest(['variant_id' => 123])->assertSessionHasErrors('variant_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithAVariantForADifferentProduct(): void
    {
        $variant = $this->create(ShopProductVariant::class);

        $this->makeRequest(['variant_id' => $variant->id])->assertSessionHasErrors('variant_id');
    }

    #[Test]
    public function itReturnsAValidationErrorIfTheVariantIsntLive(): void
    {
        $this->variant->update(['live' => false]);

        $this->makeRequest()->assertSessionHasErrors('variant_id');
    }

    #[Test]
    public function itReturnsAValidationErrorWithAnInvalidQuantity(): void
    {
        $this->makeRequest(['quantity' => null])->assertSessionHasErrors('quantity');
        $this->makeRequest(['quantity' => 'foo'])->assertSessionHasErrors('quantity');
        $this->makeRequest(['quantity' => true])->assertSessionHasErrors('quantity');
        $this->makeRequest(['quantity' => -1])->assertSessionHasErrors('quantity');
    }

    #[Test]
    public function itReturnsAValidationErrorIfTheProductVariantDoesntHaveTheRequestedQuantity(): void
    {
        $this->variant->update(['quantity' => 1]);

        $this->makeRequest(['quantity' => 2])->assertSessionHasErrors('quantity');
    }

    #[Test]
    public function itReturnsAValidationErrorWithADodgyScientificNumber(): void
    {
        $this->makeRequest(['quantity' => '123e4'])->assertSessionHasErrors('quantity');
    }

    #[Test]
    public function itReturnsAValidationErrorIfIncludeAddOnIsNotABoolean(): void
    {
        $this->makeRequest(['include_add_on' => 'foo'])->assertSessionHasErrors('include_add_on');
        $this->makeRequest(['include_add_on' => 123])->assertSessionHasErrors('include_add_on');
    }

    #[Test]
    public function itReturnsAValidationErrorIfIncludeAddOnIsTrueButTheProductDoesntHaveAnAddOn(): void
    {
        $this->assertNull($this->product->addOns);

        $this->makeRequest(['include_add_on' => true])
            ->assertSessionHasErrors(['include_add_on' => 'This product does not have an add on.']);
    }

    #[Test]
    public function itCallsTheResolveBasketAction(): void
    {
        $this->expectAction(ResolveBasketAction::class);

        $this->makeRequest();
    }

    #[Test]
    public function itPassesTheCookieBasketTokenToTheResolveBasketAction(): void
    {
        $order = $this->create(ShopOrder::class);

        $this->expectAction(ResolveBasketAction::class, [$order->token]);
        $this->withCookie('basket_token', $order->token)->makeRequest();

        $this->assertDatabaseCount(ShopOrder::class, 1);
    }

    #[Test]
    public function itReturnsTheBasketTokenInACookie(): void
    {
        $request = $this->makeRequest();

        $order = ShopOrder::query()->first();

        $request->assertCookie('basket_token', $order->token);
    }

    #[Test]
    public function itCallsTheAddProductToBasketAction(): void
    {
        $this->expectAction(AddProductToBasketAction::class, [function ($order, $product, $variant, $quantity, $includeAddOn) {
            $this->assertTrue($this->product->is($product));
            $this->assertTrue($this->variant->is($variant));
            $this->assertEquals(1, $quantity);
            $this->assertTrue($includeAddOn);

            return true;
        }]);

        $this->build(ShopProductAddOn::class)->forProduct($this->product)->create();

        $this->makeRequest(['include_add_on' => true]);
    }

    #[Test]
    public function itUpdatesTheBasketUpdatedAtTime(): void
    {
        TestTime::freeze();
        $order = $this->create(ShopOrder::class);

        TestTime::addMinutes(5);
        $this->withCookie('basket_token', $order->token)->makeRequest();

        $this->assertTrue($order->refresh()->updated_at->isSameSecond(now()));
    }

    #[Test]
    public function itRedirectsBackOnSuccess(): void
    {
        $this->from(route('shop.category', ['category' => $this->product->categories()->first()->slug]))
            ->makeRequest()
            ->assertRedirectToRoute('shop.category', ['category' => $this->product->categories()->first()->slug]);
    }

    protected function makeRequest(array $data = []): TestResponse
    {
        $data = array_merge([
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
        ], $data);

        return $this->put(route('shop.basket.add'), ShopAddBasketRequestFactory::new()->create($data));
    }
}
