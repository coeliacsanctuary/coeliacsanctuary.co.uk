<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('shop_payment_refunds', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('payment_id');
            $table->string('refund_id');
            $table->unsignedInteger('amount');
            $table->text('note');
            $table->text('response');
            $table->timestamps();
        });
    }
};
