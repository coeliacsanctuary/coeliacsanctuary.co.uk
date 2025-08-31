<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table): void {
            $table->boolean('has_digital_products')->default(false)->after('token');
            $table->boolean('is_digital_only')->default(false)->after('has_digital_products');
        });
    }
};
