<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $this->removeDuplicateUsageRows();

        Schema::table('shop_discount_codes_used', function (Blueprint $table): void {
            $table->unique('order_id');
        });
    }

    protected function removeDuplicateUsageRows(): void
    {
        $idsToKeep = DB::table('shop_discount_codes_used')
            ->selectRaw('MIN(id) as id')
            ->groupBy('order_id')
            ->pluck('id');

        DB::table('shop_discount_codes_used')
            ->whereNotIn('id', $idsToKeep)
            ->delete();
    }
};
