<?php

declare(strict_types=1);

use App\Models\Shop\ShopProduct;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_order_review_items', function (Blueprint $table): void {
            $table->string('product_type')->after('product_id');

            $table->index(['product_type', 'product_id']);

            if (Schema::hasIndex('shop_order_review_items', 'shop_order_review_items_product_id_index')) {
                $table->dropIndex('shop_order_review_items_product_id_index');
            }
        });

        DB::table('shop_order_review_items')->update(['product_type' => ShopProduct::class]);
    }
};
