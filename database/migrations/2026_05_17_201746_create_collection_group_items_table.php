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
        Schema::create('collection_group_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('collection_group_id')->constrained('collection_groups')->cascadeOnDelete();
            $table->unsignedBigInteger('item_id');
            $table->string('item_type');
            $table->string('item_title')->nullable();
            $table->text('item_description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_group_items');
    }
};
