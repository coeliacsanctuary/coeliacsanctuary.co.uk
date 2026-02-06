<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shop_order_download_links', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('order_id');
            $table->dateTime('expires_at');
            $table->timestamps();
        });
    }
};
