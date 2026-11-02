<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Signature;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopMassDiscount;
use App\Models\Shop\ShopProduct;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mattiasgeniar\Percentage\Percentage;

#[Signature('coeliac:apply-mass-discounts')]
class ApplyMassDiscountsCommand extends Command
{
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
                        $product->prices()->create([
                            'price' => $product->currentPrice - Percentage::of($discount->percentage, (int) $product->currentPrice),
                            'sale_price' => true,
                            'start_at' => $discount->start_at,
                            'end_at' => $discount->end_at,
                        ]);
                    });
                });

                $discount->update(['created' => true]);
            });
    }
}
