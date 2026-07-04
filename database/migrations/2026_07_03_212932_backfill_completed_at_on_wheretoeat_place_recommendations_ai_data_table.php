<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        DB::table('wheretoeat_place_recommendations_ai_data')
            ->whereNull('completed_at')
            ->whereNull('failed_at')
            ->whereNotNull('explanation')
            ->update(['completed_at' => DB::raw('created_at')]);
    }
};
