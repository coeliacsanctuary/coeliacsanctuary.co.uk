<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('wheretoeat_place_recommendations_ai_data', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('wheretoeat_place_recommendation_id');
            $table->string('place_name')->nullable();
            $table->text('place_address')->nullable();
            $table->string('place_country')->nullable();
            $table->string('place_county')->nullable();
            $table->string('place_town')->nullable();
            $table->string('place_area')->nullable();
            $table->string('latitude', 50)->nullable();
            $table->string('longitude', 50)->nullable();
            $table->string('phone_number')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('eatery_type')->nullable();
            $table->string('venue_type')->nullable();
            $table->string('cuisine')->nullable();
            $table->text('info')->nullable();
            $table->json('features')->nullable();
            $table->text('explanation');
            $table->boolean('is_eligible');
            $table->timestamps();
        });
    }
};
