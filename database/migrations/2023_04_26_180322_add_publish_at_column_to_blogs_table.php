<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table): void {
            $table->timestamp('publish_at')
                ->default(Carbon::now())
                ->after('meta_description');
        });

        DB::statement('UPDATE blogs SET publish_at = created_at');
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table): void {
            $table->dropColumn('publish_at');
        });
    }
};
