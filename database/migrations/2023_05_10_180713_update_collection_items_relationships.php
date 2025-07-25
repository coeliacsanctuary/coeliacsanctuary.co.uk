<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('UPDATE collection_items SET item_type = "App\\\Models\\\Recipes\\\Recipe" WHERE item_type = "Coeliac\\\Modules\\\Recipe\\\Models\\\Recipe"');
        DB::statement('UPDATE collection_items SET item_type = "App\\\Models\\\Blogs\\\Blog" WHERE item_type = "Coeliac\\\Modules\\\Blog\\\Models\\\Blog"');
        DB::statement('delete from collection_items where item_type like "Coeliac%"');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
