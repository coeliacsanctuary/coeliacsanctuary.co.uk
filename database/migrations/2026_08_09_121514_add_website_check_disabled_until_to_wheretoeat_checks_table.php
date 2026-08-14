<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('wheretoeat_checks', function (Blueprint $table): void {
            $table->timestamp('website_check_disabled_until')->nullable()->after('website_checked_at');
        });

        DB::table('wheretoeat_checks')
            ->where('disable_website_check', true)
            ->update(['website_check_disabled_until' => now()->addMonths(6)]);

        Schema::table('wheretoeat_checks', function (Blueprint $table): void {
            $table->dropColumn('disable_website_check');
        });
    }
};
