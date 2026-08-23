<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\Shop\VerifyDiscountCodeAction;
use App\Models\Shop\ShopDiscountCode;
use App\Models\Shop\ShopDiscountCodesUsed;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use RuntimeException;
use Tests\TestCase;

class VerifyDiscountCodeActionTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopProduct $product;

    protected ShopProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts(1, 1);
        $this->order = ShopOrder::query()->create();
        $this->product = ShopProduct::query()->first();
        $this->variant = $this->product->variants->first();

        $this->item = $this->create(ShopOrderItem::class, [
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'product_variant_id' => $this->variant->id,
            'product_price' => 200,
        ]);
    }

    #[Test]
    public function itErrorsIfTheDiscountCodeHasExpired(): void
    {
        $code = $this->build(ShopDiscountCode::class)->expired()->create(['code' => 'foobar']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This discount code is not currently active');

        app(VerifyDiscountCodeAction::class)->handle($code, $this->order->token);
    }

    #[Test]
    public function itErrorsIfTheDiscountCodeHasntStartedYet(): void
    {
        $code = $this->build(ShopDiscountCode::class)->startsTomorrow()->create(['code' => 'foobar']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This discount code is not currently active');

        app(VerifyDiscountCodeAction::class)->handle($code, $this->order->token);
    }

    #[Test]
    public function itDoesntErrorForACodeInsideItsDateWindow(): void
    {
        $code = $this->create(ShopDiscountCode::class, ['code' => 'foobar', 'min_spend' => 1]);

        app(VerifyDiscountCodeAction::class)->handle($code, $this->order->token);

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function itErrorsIfTheDiscountCodeIsValidButHasHadTooManyClaims(): void
    {
        $code = $this->create(ShopDiscountCode::class, [
            'code' => 'foobar',
            'max_claims' => 1,
        ]);

        $this->build(ShopDiscountCodesUsed::class)->forDiscountCode($code)->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Discount Code has had too many claims');

        app(VerifyDiscountCodeAction::class)->handle($code, $this->order->token);
    }

    #[Test]
    public function itErrorsIfTheDiscountCodeIsValidButTheOrderDoesntMeetTheMinimumSpend(): void
    {
        $code = $this->create(ShopDiscountCode::class, [
            'code' => 'foobar',
            'min_spend' => 1000,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Your basket doesnt meet the minimum value for this discount code, please add items worth £8.00 to qualify.');

        app(VerifyDiscountCodeAction::class)->handle($code, $this->order->token);
    }
}
