<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('wheretoeat_areas', function (Blueprint $table): void {
            $table->id();
            $table->string('area');
            $table->string('slug');
            $table->string('latlng')->nullable();
            $table->unsignedBigInteger('town_id')->index();
            $table->timestamps();
        });
    }
};
