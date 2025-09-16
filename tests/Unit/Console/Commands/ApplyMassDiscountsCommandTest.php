<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\ApplyMassDiscountsCommand;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopMassDiscount;
use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplyMassDiscountsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withCategoriesAndProducts();
    }

    #[Test]
    public function itDoesntDoAnythingIfThereAreNoMassDiscountsToProcess(): void
    {
        $count = ShopPrice::query()->count();

        $this->artisan(ApplyMassDiscountsCommand::class);

        $this->assertDatabaseCount(ShopPrice::class, $count);
    }

    #[Test]
    public function itDoesntProcessMassDiscountsWithAStartDateInTheFuture(): void
    {
        $this->create(ShopMassDiscount::class, [
            'start_at' => Carbon::now()->addWeek(),
        ]);

        $count = ShopPrice::query()->count();

        $this->artisan(ApplyMassDiscountsCommand::class);

        $this->assertDatabaseCount(ShopPrice::class, $count);
    }

    #[Test]
    public function itCreatesASalePriceForEachProductInACategoryLinkedToAMassDiscount(): void
    {
        $category = ShopCategory::query()->first();

        $this->build(ShopMassDiscount::class)
            ->forCategory($category)
            ->create([
                'start_at' => Carbon::now()->subMinute(),
            ]);

        $category->products->each(function (ShopProduct $product): void {
            $product->variants->each(function (ShopProductVariant $variant): void {
                $this->assertCount(1, $variant->prices);
            });
        });

        $this->artisan(ApplyMassDiscountsCommand::class);

        $category->refresh();

        $category->products->each(function (ShopProduct $product): void {
            $product->variants->each(function (ShopProductVariant $variant): void {
                $this->assertCount(2, $variant->prices);
                $this->assertCount(1, $variant->prices->where('sale_price', true));
            });
        });
    }

    #[Test]
    public function itDoesntCreateASalePriceForProductsInAnotherCategory(): void
    {
        $category = ShopCategory::query()->first();
        $otherCategory = ShopCategory::query()->where('id', '!=', $category->id)->first();

        $this->build(ShopMassDiscount::class)
            ->forCategory($category)
            ->create([
                'start_at' => Carbon::now()->subMinute(),
            ]);

        $category->products->each(function (ShopProduct $product): void {
            $product->variants->each(function (ShopProductVariant $variant): void {
                $this->assertCount(1, $variant->prices);
            });
        });

        $otherCategory->products->each(function (ShopProduct $product): void {
            $product->variants->each(function (ShopProductVariant $variant): void {
                $this->assertCount(1, $variant->prices);
            });
        });

        $this->artisan(ApplyMassDiscountsCommand::class);

        $category->refresh();

        $category->products->each(function (ShopProduct $product): void {
            $product->variants->each(function (ShopProductVariant $variant): void {
                $this->assertCount(2, $variant->prices);
            });
        });

        $otherCategory->products()->each(function (ShopProduct $product): void {
            $product->variants->each(function (ShopProductVariant $variant): void {
                $this->assertCount(1, $variant->prices);
            });
        });
    }

    #[Test]
    public function itDoesntProcessMassDiscountsThatHaveAlreadyBeenProcessed(): void
    {
        $category = ShopCategory::query()->first();

        $this->build(ShopMassDiscount::class)
            ->forCategory($category)
            ->alreadyProcessed()
            ->create([
                'start_at' => Carbon::now()->subMinute(),
            ]);

        $category->products->each(function (ShopProduct $product): void {
            $product->variants->each(function (ShopProductVariant $variant): void {
                $this->assertCount(1, $variant->prices);
            });
        });

        $this->artisan(ApplyMassDiscountsCommand::class);

        $category->refresh();

        $category->products->each(function (ShopProduct $product): void {
            $product->variants->each(function (ShopProductVariant $variant): void {
                $this->assertCount(1, $variant->prices);
            });
        });
    }
}
