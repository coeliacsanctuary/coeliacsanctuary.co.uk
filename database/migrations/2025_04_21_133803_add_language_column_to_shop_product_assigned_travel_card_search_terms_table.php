<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_product_assigned_travel_card_search_terms', function (Blueprint $table): void {
            $table->string('card_language')->nullable()->after('product_id');
            $table->string('card_score')->nullable()->after('product_id');
            $table->boolean('card_show_on_product_page')->default(false)->after('product_id');
        });
    }
};
