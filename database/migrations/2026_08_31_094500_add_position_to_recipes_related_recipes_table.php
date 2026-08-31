<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('recipes_related_recipes', function (Blueprint $table): void {
            $table->unsignedInteger('position')->after('related_recipe_id')->default(0);
        });

        DB::statement('
            UPDATE recipes_related_recipes
            JOIN (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY recipe_id ORDER BY related_recipe_id) - 1 AS resolved_position
                FROM recipes_related_recipes
            ) AS ordered ON ordered.id = recipes_related_recipes.id
            SET recipes_related_recipes.position = ordered.resolved_position
        ');
    }
};
