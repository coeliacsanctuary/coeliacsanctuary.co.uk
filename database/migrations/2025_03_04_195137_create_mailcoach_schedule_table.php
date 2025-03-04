<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('mailcoach_schedule', function (Blueprint $table): void {
            $table->id();
            $table->datetime('scheduled_at');
            $table->timestamps();
        });
    }
};
