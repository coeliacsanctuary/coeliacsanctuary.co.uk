<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shop_customs_fees', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('postage_country_id')->index();
            $table->unsignedInteger('fee');
            $table->string('description', 255)->nullable();
            $table->timestamps();
        });
    }
};
