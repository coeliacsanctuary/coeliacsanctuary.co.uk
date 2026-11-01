<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('wheretoeat_nationwide_branches')) {
            return;
        }

        Schema::create('wheretoeat_nationwide_branches', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('wheretoeat_id');
            $table->string('name')->nullable();
            $table->string('slug')->index();
            $table->unsignedInteger('country_id')->index();
            $table->unsignedInteger('county_id')->index();
            $table->unsignedInteger('town_id')->index();
            $table->text('address');
            $table->string('lat', 50);
            $table->string('lng', 50);
            $table->boolean('live');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wheretoeat_nationwide_branches');
    }
};
