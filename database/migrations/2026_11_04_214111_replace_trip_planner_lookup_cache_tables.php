<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('open_route_service_autocomplete_cache');

        Schema::create('google_places_autocomplete_cache', function (Blueprint $table): void {
            $table->id();
            $table->string('term')->unique();
            $table->text('response');
            $table->integer('hits')->default(1);
            $table->dateTime('last_fetched_at');
            $table->timestamps();
        });

        Schema::create('google_places_details_cache', function (Blueprint $table): void {
            $table->id();
            $table->string('place_id')->unique();
            $table->text('response');
            $table->integer('hits')->default(1);
            $table->dateTime('last_fetched_at');
            $table->timestamps();
        });
    }
};
