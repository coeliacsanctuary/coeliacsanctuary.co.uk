<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('wheretoeat_sealiac_overview', function (Blueprint $table): void {
            $table->boolean('invalidated')->default(false)->after('overview');
        });
    }
};
