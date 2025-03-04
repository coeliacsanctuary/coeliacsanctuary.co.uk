<?php

declare(strict_types=1);

use App\Models\Shop\ShopProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_products', function (Blueprint $table): void {
            $table->string('legacy_slug')->after('slug')->unique()->nullable();
        });

        ShopProduct::query()
            ->whereHas('categories', fn (Builder $query) => $query->where('category_id', 1))
            ->get()
            ->each(function (ShopProduct $product): void {
                $product->legacy_slug = $product->slug;
                $product->title = Str::of($product->title)->replace('Gluten Free Language Travel Cards', 'Coeliac Gluten Free Travel Translation Card');
                $product->slug = Str::slug($product->title);

                $product->saveQuietly();
            });

        ShopProduct::query()
            ->whereHas('categories', fn (Builder $query) => $query->where('category_id', 11))
            ->get()
            ->each(function (ShopProduct $product): void {
                $product->legacy_slug = $product->slug;
                $product->title = Str::of($product->title)->replace('Coeliac+ Travel Language Card', 'Coeliac+ Gluten Free and Other Dietary Needs Travel Translation Card');
                $product->slug = Str::of($product->title)->replace('+', ' plus')->slug();

                $product->saveQuietly();
            });
    }
};
