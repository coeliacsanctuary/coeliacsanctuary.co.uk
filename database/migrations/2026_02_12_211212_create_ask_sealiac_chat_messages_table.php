<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('ask_sealiac_chat_messages', function (Blueprint $table): void {
            $table->id();
            $table->text('prompt');
            $table->text('response');
            $table->json('tool_uses');
            $table->foreignId('ask_sealiac_chat_id');
            $table->timestamps();
        });
    }
};
