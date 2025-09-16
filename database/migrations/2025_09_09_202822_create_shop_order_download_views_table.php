<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shop_order_download_views', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('shop_order_download_link_id');
            $table->timestamps();
        });
    }
};
