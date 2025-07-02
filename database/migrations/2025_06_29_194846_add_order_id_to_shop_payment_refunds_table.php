<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_payment_refunds', function (Blueprint $table): void {
            $table->unsignedBigInteger('order_id')->after('payment_id');
            $table->string('refund_id')->nullable()->change();
            $table->text('response')->nullable()->change();
        });
    }
};
