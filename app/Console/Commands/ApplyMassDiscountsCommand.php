<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopMassDiscount;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mattiasgeniar\Percentage\Percentage;

class ApplyMassDiscountsCommand extends Command
{
    protected $signature = 'coeliac:apply-mass-discounts';

    public function handle(): void
    {
        ShopMassDiscount::query()
            ->whereDate('start_at', '<=', Carbon::now())
            ->where('created', false)
            ->with(['assignedCategories', 'assignedCategories.products'])
            ->get()
            ->each(function (ShopMassDiscount $discount): void {
                $discount->assignedCategories->each(function (ShopCategory $category) use ($discount): void {
                    $category->products->each(function (ShopProduct $product) use ($discount): void {
                        $product->variants->each(function (ShopProductVariant $variant) use ($discount): void {
                            $variant->prices()->create([
                                'product_id' => $variant->product_id,
                                'price' => $variant->currentPrice - Percentage::of($discount->percentage, (int) $variant->currentPrice),
                                'sale_price' => true,
                                'start_at' => $discount->start_at,
                                'end_at' => $discount->end_at,
                            ]);
                        });
                    });
                });

                $discount->update(['created' => true]);
            });
    }
}
