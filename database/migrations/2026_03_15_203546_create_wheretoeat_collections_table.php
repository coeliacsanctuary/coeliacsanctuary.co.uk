<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('wheretoeat_collections', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('meta_tags');
            $table->text('meta_description');
            $table->text('description');
            $table->text('body');
            $table->json('configuration');
            $table->text('eatery_query');
            $table->text('branch_query');
            $table->boolean('live');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wheretoeat_collections');
    }
};
