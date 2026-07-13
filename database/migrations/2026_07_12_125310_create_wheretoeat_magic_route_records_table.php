<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('wheretoeat_magic_route_records', function (Blueprint $table): void {
            $table->id();
            $table->string('resolver_type');
            $table->string('raw_location');
            $table->nullableMorphs('location');
            $table->text('builder_config');
            $table->text('body')->nullable();
            $table->timestamps();
        });
    }
};
