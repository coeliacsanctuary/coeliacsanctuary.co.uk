<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_customers', function (Blueprint $table): void {
            $table->string('remember_token')->nullable()->after('one_time_code_expires_at');
        });
    }
};
