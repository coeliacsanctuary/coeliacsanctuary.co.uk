<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('recipe_metrics', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('recipe_id');
            $table->date('date');
            $table->unsignedInteger('page_views')->default(0);
            $table->unsignedInteger('page_comment_views')->default(0);
            $table->unsignedInteger('detail_card_views')->default(0);
            $table->unsignedInteger('collection_card_views')->default(0);
            $table->timestamps();

            $table->unique(['recipe_id', 'date']);
        });
    }
};
