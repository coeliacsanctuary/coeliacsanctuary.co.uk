<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_order_items', function (Blueprint $table): void {
            $table->unsignedBigInteger('product_add_on_id')
                ->nullable()
                ->after('product_variant_id');

            $table->string('product_add_on_title')
                ->nullable()
                ->after('product_price');

            $table->integer('product_add_on_price')
                ->nullable()
                ->after('product_add_on_title');
        });
    }
};
