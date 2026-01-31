<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shop_product_add_ons', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->foreignId('product_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_product_add_ons');
    }
};
