<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_payments', function (Blueprint $table): void {
            $table->unsignedInteger('custom_fees')->default(0)->after('discount');
        });
    }
};
