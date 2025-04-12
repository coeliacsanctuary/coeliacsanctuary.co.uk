<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('google_static_maps', function (Blueprint $table): void {
            $table->id();
            $table->uuid()->unique();
            $table->string('latlng')->index();
            $table->integer('hits')->default(0);
            $table->dateTime('last_fetched_at')->default(now());
            $table->timestamps();
        });
    }
};
