<?php

declare(strict_types=1);

use App\Enums\Shop\ProductVariantType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_product_variants', function (Blueprint $table): void {
            $table->string('variant_type', 8)->default(ProductVariantType::PHYSICAL->value)->after('product_id');
        });
    }
};
