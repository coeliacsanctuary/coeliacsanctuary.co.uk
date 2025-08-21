<?php

declare(strict_types=1);

use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('shop_product_prices', function (Blueprint $table): void {
            $table->unsignedBigInteger('variant_id')->after('product_id')->nullable()->index();
        });

        ShopProduct::query()
            ->with(['variants', 'prices'])
            ->get()
            ->each(function (ShopProduct $product): void {
                $product->prices->each(function (ShopPrice $price) use ($product): void {
                    if ($price->variant_id) {
                        return;
                    }

                    $price->update(['variant_id' => $product->variants->first()->id]);

                    if ($product->variants->count() > 1) {
                        $product->variants->slice(1)->each(function (ShopProductVariant $variant) use ($product, $price): void {
                            $product->prices()->create([
                                ...Arr::except($price->toArray(), ['id', 'product_id', 'variant_id', 'created_at', 'updated_at']),
                                'variant_id' => $variant->id,
                            ]);
                        });
                    }
                });
            });

        Schema::rename('shop_product_prices', 'shop_prices');
    }
};
