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
        Schema::table('collections', function (Blueprint $table): void {
            $table->boolean('live')
                ->default(true)
                ->after('body');
        });

        Schema::table('collections', function (Blueprint $table): void {
            $table->boolean('draft')
                ->default(false)
                ->after('live');
        });

        Schema::table('collections', function (Blueprint $table): void {
            $table->timestamp('publish_at')
                ->default(Carbon::now())
                ->after('draft');
        });

        DB::statement('UPDATE collections SET publish_at = created_at');
    }
};
