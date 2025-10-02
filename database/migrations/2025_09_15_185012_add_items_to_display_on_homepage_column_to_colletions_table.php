<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table): void {
            $table->unsignedTinyInteger('items_to_display')->default(3)->after('remove_from_homepage');
        });
    }
};
