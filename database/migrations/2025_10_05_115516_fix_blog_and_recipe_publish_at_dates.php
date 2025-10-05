<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table): void {
            $table->timestamp('publish_at')
                ->nullable()
                ->useCurrent()
                ->change();
        });

        Schema::table('recipes', function (Blueprint $table): void {
            $table->timestamp('publish_at')
                ->nullable()
                ->useCurrent()
                ->change();
        });
    }
};
