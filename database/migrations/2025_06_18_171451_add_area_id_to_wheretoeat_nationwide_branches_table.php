<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('wheretoeat_nationwide_branches', function (Blueprint $table): void {
            $table->unsignedBigInteger('area_id')->index()->nullable()->after('town_id');
        });
    }
};
