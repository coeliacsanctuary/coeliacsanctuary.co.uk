<?php

declare(strict_types=1);

use App\Enums\Shop\DigitalCardType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_digital_cards', function (Blueprint $table): void {
            $table->foreignId('type_id')->after('id');
        });

        DB::table('shop_digital_cards')
            ->where('type', 'standard')
            ->update(['type_id' => DigitalCardType::STANDARD->value]);

        DB::table('shop_digital_cards')
            ->where('type', 'plus')
            ->update(['type_id' => DigitalCardType::PLUS->value]);

        Schema::table('shop_digital_cards', function (Blueprint $table): void {
            $table->dropColumn('type');
        });
    }
};
