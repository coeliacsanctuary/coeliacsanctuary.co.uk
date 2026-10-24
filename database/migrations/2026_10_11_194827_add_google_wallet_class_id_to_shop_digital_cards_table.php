<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_digital_cards', function (Blueprint $table): void {
            $table->string('google_wallet_class_id')->nullable()->after('enable_physical_addons');
        });
    }
};
