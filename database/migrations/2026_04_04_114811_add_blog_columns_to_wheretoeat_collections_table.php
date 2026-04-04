<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('wheretoeat_collections', function (Blueprint $table): void {
            $table->boolean('cross_post_to_blogs')->default(false)->after('live');
            $table->unsignedBigInteger('blog_id')->nullable()->after('cross_post_to_blogs');
        });
    }
};
