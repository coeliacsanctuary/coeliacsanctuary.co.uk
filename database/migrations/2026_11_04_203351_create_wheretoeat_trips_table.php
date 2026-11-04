<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('wheretoeat_trips', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 8)->unique();
            $table->string('edit_token', 40);
            $table->json('points');
            $table->unsignedTinyInteger('distance');
            $table->mediumText('geometry');
            $table->double('route_distance');
            $table->double('route_duration');
            $table->json('timings');
            $table->dateTime('last_visited_at')->nullable();
            $table->timestamps();
        });
    }
};
