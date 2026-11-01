<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table): void {
            $table->timestamp('publish_at')
                ->default(Carbon::now())
                ->after('live');
        });

        DB::statement('UPDATE recipes SET publish_at = created_at');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table): void {
            $table->dropColumn('publish_at');
        });
    }
};
