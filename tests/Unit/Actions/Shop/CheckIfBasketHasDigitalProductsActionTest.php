<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\CheckIfBasketHasDigitalProductsAction;
use App\Enums\Shop\ProductVariantType;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProductVariant;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckIfBasketHasDigitalProductsActionTest extends TestCase
{
    #[Test]
    public function itSetsHasDigitalProductsToTrueIfTheBasketHasDigitalProducts(): void
    {
        foreach ([ProductVariantType::DIGITAL, ProductVariantType::BUNDLE] as $variantType) {
            $basket = $this->build(ShopOrder::class)
                ->asBasket()
                ->create();

            $variant = $this->build(ShopProductVariant::class)
                ->has($this->build(ShopPrice::class), 'prices')
                ->create([
                    'variant_type' => $variantType,
                ]);

            $this->build(ShopOrderItem::class)
                ->add($variant)
                ->toBasket($basket)
                ->create();

            $this->assertFalse($basket->has_digital_products);

            app(CheckIfBasketHasDigitalProductsAction::class)->handle($basket);

            $basket->refresh();

            $this->assertTrue($basket->has_digital_products);
        }
    }

    #[Test]
    public function itSetsHasDigitalProductsToFalseIfTheBasketNoLongerHasDigitalProducts(): void
    {
        $basket = $this->build(ShopOrder::class)
            ->asBasket()
            ->hasDigitalProducts()
            ->create();

        $variant = $this->build(ShopProductVariant::class)
            ->has($this->build(ShopPrice::class), 'prices')
            ->create([
                'variant_type' => ProductVariantType::PHYSICAL,
            ]);

        $this->build(ShopOrderItem::class)
            ->add($variant)
            ->toBasket($basket)
            ->create();

        $this->assertTrue($basket->has_digital_products);

        app(CheckIfBasketHasDigitalProductsAction::class)->handle($basket);

        $basket->refresh();

        $this->assertFalse($basket->has_digital_products);
    }

    #[Test]
    public function itSetsIsDigitalOnlyToTrueIfTheBasketOnlyHasDigitalProducts(): void
    {
        $basket = $this->build(ShopOrder::class)
            ->asBasket()
            ->create();

        $variant = $this->build(ShopProductVariant::class)
            ->has($this->build(ShopPrice::class), 'prices')
            ->create([
                'variant_type' => ProductVariantType::DIGITAL,
            ]);

        $this->build(ShopOrderItem::class)
            ->add($variant)
            ->toBasket($basket)
            ->create();

        $this->assertFalse($basket->is_digital_only);

        app(CheckIfBasketHasDigitalProductsAction::class)->handle($basket);

        $basket->refresh();

        $this->assertTrue($basket->is_digital_only);
    }

    #[Test]
    public function itSetsDigitalOnlyToFalseIfTheBasketDoesntOnlyHaveDigitalProducts(): void
    {
        foreach ([ProductVariantType::PHYSICAL, ProductVariantType::BUNDLE] as $variantType) {
            $basket = $this->build(ShopOrder::class)
                ->asBasket()
                ->isDigitalOnly()
                ->create();

            $variant = $this->build(ShopProductVariant::class)
                ->has($this->build(ShopPrice::class), 'prices')
                ->create([
                    'variant_type' => $variantType,
                ]);

            $this->build(ShopOrderItem::class)
                ->add($variant)
                ->toBasket($basket)
                ->create();

            $this->assertTrue($basket->is_digital_only);

            app(CheckIfBasketHasDigitalProductsAction::class)->handle($basket);

            $basket->refresh();

            $this->assertFalse($basket->is_digital_only);
        }
    }
}
