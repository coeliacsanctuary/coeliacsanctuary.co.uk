<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_product_variants', function (Blueprint $table): void {
            $table->json('icon')->nullable()->default(null)->after('quantity');
        });
    }
};
