<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shop_order_digital_items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('digital_card_id');
            $table->string('digital_card_title');
            $table->integer('digital_card_price');
            $table->unsignedBigInteger('physical_order_item_id')->nullable();
            $table->timestamps();
        });
    }
};
