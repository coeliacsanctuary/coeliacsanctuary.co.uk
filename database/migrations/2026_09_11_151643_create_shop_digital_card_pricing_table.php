<?php

declare(strict_types=1);

use App\Models\Shop\ShopDigitalCardPrice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shop_digital_card_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('digital_card_id')->default(0);
            $table->unsignedInteger('price')->default(100);
            $table->unsignedInteger('price_with_physical_product')->default(25);
            $table->timestamps();
        });

        ShopDigitalCardPrice::query()->create([
            'price' => 100,
            'price_with_physical_product' => 25,
        ]);
    }
};
