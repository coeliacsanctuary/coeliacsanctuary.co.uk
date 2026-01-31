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
        Schema::table('shop_product_prices', function (Blueprint $table): void {
            $table->string('purchasable_type')->after('id');
            $table->unsignedBigInteger('purchasable_id')->after('id');

            $table->index(['purchasable_type', 'purchasable_id']);

            if ( ! app()->runningUnitTests()) {
                $table->dropForeign('shop_product_prices_ibfk_1');
            } else {
                $table->dropForeign('shop_product_prices_product_id_foreign');
            }

            $table->rename('shop_prices');
        });

        DB::table('shop_prices')
            ->update([
                'purchasable_type' => ShopProduct::class,
                'purchasable_id' => DB::raw('product_id'),
            ]);

        Schema::table('shop_prices', function (Blueprint $table): void {
            $table->dropColumn('product_id');
        });
    }
};
