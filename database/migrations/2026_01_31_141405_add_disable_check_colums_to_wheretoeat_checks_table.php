<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('wheretoeat_checks', function (Blueprint $table): void {
            $table->boolean('disable_website_check')->default(false)->after('website_checked_at');
            $table->boolean('disable_google_check')->default(false)->after('google_checked_at');
        });
    }
};
