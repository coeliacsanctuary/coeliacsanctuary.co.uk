<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('wheretoeat_place_recommendation', function (Blueprint $table): void {
            $table->boolean('ignored')->default(false)->after('completed');
        });
    }
};
