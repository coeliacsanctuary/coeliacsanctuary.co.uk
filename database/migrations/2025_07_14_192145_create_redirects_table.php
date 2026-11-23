<?php

declare(strict_types=1);

use Illuminate\Http\Response;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('from')->unique();
            $table->string('to');
            $table->integer('status')->default(Response::HTTP_MOVED_PERMANENTLY);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamps();
        });
    }
};
