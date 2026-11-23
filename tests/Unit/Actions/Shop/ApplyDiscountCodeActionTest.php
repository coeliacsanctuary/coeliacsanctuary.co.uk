<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\Shop\ApplyDiscountCodeAction;
use App\Actions\Shop\VerifyDiscountCodeAction;
use App\Enums\Shop\DiscountCodeType;
use App\Models\Shop\ShopDiscountCode;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Tests\TestCase;

class ApplyDiscountCodeActionTest extends TestCase
{
    protected ShopDiscountCode $discountCode;

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
            'quantity' => 2,
        ]);

        $this->discountCode = $this->create(ShopDiscountCode::class, [
            'code' => 'foobar',
        ]);
    }

    #[Test]
    public function itVerifiesTheDiscountCode(): void
    {
        $this->expectAction(VerifyDiscountCodeAction::class);

        app(ApplyDiscountCodeAction::class)->handle($this->discountCode, $this->order->token);
    }

    #[Test]
    public function itReturnsTheDeductionAmountIfTypeIsMoneyDeduction(): void
    {
        $this->discountCode->update([
            'type_id' => DiscountCodeType::MONEY,
            'deduction' => 123,
        ]);

        $this->assertEquals(123, app(ApplyDiscountCodeAction::class)->handle($this->discountCode, $this->order->token));
    }

    #[Test]
    public function itReturnsTheDiscountAsAPercentageOfTheRunningSubtotalIfDiscountCodeTypeIsPercentage(): void
    {
        $this->discountCode->update([
            'type_id' => DiscountCodeType::PERCENTAGE,
            'deduction' => 15,
        ]);

        $this->assertEquals(60, app(ApplyDiscountCodeAction::class)->handle($this->discountCode, $this->order->token));
    }
}
