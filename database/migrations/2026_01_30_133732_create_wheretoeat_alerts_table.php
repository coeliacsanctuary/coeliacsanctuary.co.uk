<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('wheretoeat_alerts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('wheretoeat_id')->index();
            $table->string('type');
            $table->text('details');
            $table->boolean('completed')->default(false);
            $table->boolean('ignored')->default(false);
            $table->timestamps();
        });
    }
};
