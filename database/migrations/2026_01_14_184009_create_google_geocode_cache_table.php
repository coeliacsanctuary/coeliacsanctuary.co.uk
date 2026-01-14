<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('google_geocode_cache', function (Blueprint $table): void {
            $table->id();
            $table->string('term');
            $table->text('response');
            $table->integer('hits')->default(1);
            $table->dateTime('most_recent_hit')->default(now());
            $table->timestamps();
        });
    }
};
