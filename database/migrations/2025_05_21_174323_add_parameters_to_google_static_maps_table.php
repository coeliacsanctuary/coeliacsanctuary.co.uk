<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('google_static_maps', function (Blueprint $table): void {
            $table->text('parameters')->nullable()->after('latlng');
        });
    }
};
