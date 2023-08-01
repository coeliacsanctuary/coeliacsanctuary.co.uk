<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        \App\Models\Recipes\RecipeFeature::query()
            ->where('feature', 'Slimming World Friendly')
            ->update(['feature' => 'Healthier Option', 'slug' => 'healthier-option']);
    }
};
