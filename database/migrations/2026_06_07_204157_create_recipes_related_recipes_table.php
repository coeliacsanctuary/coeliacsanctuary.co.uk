<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('recipes_related_recipes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('recipe_id');
            $table->unsignedBigInteger('related_recipe_id');
            $table->timestamps();

            $table->unique(['recipe_id', 'related_recipe_id']);
        });
    }
};
