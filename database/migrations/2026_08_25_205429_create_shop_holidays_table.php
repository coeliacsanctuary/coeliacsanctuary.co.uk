<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shop_holidays', function (Blueprint $table): void {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('ship_on');
            $table->timestamps();
        });
    }
};
