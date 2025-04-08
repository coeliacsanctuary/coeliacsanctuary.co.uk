<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('shop_orders', function (Blueprint $table): void {
            $table->boolean('sent_abandoned_basket_email')->default(false)->after('shipped_at');
        });
    }
};
