<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('wheretoeat_place_recommendations_ai_data', function (Blueprint $table): void {
            $table->text('explanation')->nullable()->change();
            $table->boolean('is_eligible')->nullable()->change();
            $table->dateTime('completed_at')->nullable()->after('is_eligible');
            $table->dateTime('failed_at')->nullable()->after('completed_at');
        });
    }
};
