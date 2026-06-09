<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_products', function (Blueprint $table): void {
            $table->boolean('google_merchant_enabled')->default(true)->after('pinned');
            $table->string('google_merchant_product_id')->nullable()->after('google_merchant_enabled');
        });
    }
};
