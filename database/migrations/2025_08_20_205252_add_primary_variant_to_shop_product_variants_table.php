<?php

declare(strict_types=1);

use App\Models\Shop\ShopProduct;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_product_variants', function (Blueprint $table): void {
            $table->boolean('primary_variant')->default(false)->after('live');
        });

        ShopProduct::query()
            ->whereRelation('categories', 'title', 'Coeliac Gluten Free Travel Cards')
            ->with(['variants'])
            ->whereNotLike('title', '%full set%')
            ->get()
            ->each(fn (ShopProduct $product) => $product->variants->first()->update(['primary_variant' => true]));
    }
};
