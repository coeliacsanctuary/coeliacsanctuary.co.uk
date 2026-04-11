<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table): void {
            $table->unsignedBigInteger('eatery_collection_id')->nullable()->after('primary_tag_id');
        });
    }
};
