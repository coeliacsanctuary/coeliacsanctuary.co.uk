<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ask_sealiac_chats', function (Blueprint $table): void {
            $table->id();
            $table->string('session_id');
            $table->string('chat_id');
            $table->text('summary')->nullable();
            $table->timestamps();

            $table->index(['session_id', 'chat_id']);
        });
    }
};
