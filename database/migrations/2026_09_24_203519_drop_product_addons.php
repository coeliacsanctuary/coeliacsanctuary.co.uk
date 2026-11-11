<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::drop('shop_product_add_ons');
        Schema::drop('shop_order_download_links');
        Schema::drop('shop_order_download_views');

        Schema::table('shop_order_items', function (Blueprint $table): void {
            $table->dropColumn('product_add_on_id');

            $table->dropColumn('product_add_on_title');

            $table->dropColumn('product_add_on_price');
        });

        Schema::table('shop_orders', function (Blueprint $table): void {
            $table->dropColumn('add_ons_sent_at');
        });
    }
};
