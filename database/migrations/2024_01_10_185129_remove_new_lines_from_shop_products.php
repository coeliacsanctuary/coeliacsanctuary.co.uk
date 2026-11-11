<?php

declare(strict_types=1);

use App\Models\Shop\ShopProduct;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        ShopProduct::query()->withoutGlobalScopes()->get()->each(function (ShopProduct $product): void {
            $product->timestamps = false;
            $product->long_description = str_replace('<br />', "\n", $product->long_description);

            $product->saveQuietly();
        });
    }
};
