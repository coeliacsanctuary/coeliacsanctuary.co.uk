<?php

declare(strict_types=1);

use App\Enums\Shop\DigitalCardType;
use App\Models\Shop\ShopDigitalCardType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shop_digital_card_types', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 100);
            $table->string('slug', 100)->unique();
            $table->foreignId('category_id');
            $table->boolean('live')->default(false);
            $table->timestamps();
        });

        ShopDigitalCardType::query()->create([
            'id' => DigitalCardType::STANDARD->value,
            'title' => DigitalCardType::STANDARD->name(),
            'slug' => 'digital-coeliac-gluten-free-travel-cards',
            'category_id' => 1,
            'live' => true,
        ]);

        ShopDigitalCardType::query()->create([
            'id' => DigitalCardType::PLUS->value,
            'title' => DigitalCardType::PLUS->name(),
            'slug' => 'digital-coeliac-plus-other-allergen-travel-cards',
            'category_id' => 11,
            'live' => false,
        ]);
    }
};
