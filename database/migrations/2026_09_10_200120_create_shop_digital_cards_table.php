<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_digital_cards', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 10);
            $table->string('title', 100);
            $table->string('slug', 100)->unique();
            $table->string('language', 50);
            $table->string('native_language', 50);
            $table->boolean('live')->default(false);
            $table->boolean('enable_physical_addons')->default(false);
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('card_title', 256);
            $table->text('card_intro');
            $table->string('cant_eat_label', 50);
            $table->text('cant_eat_items');
            $table->string('cant_eat_colour', 10)->default('red');
            $table->string('cant_eat_icon', 10)->default('cross');
            $table->string('can_eat_label', 50);
            $table->text('can_eat_items');
            $table->string('can_eat_colour', 10)->default('green');
            $table->string('can_eat_icon', 10)->default('tick');
            $table->text('card_outro');
            $table->string('card_thank_you', 256);
            $table->timestamps();
        });
    }
};
