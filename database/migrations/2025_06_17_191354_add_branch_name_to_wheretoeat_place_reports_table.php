<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('wheretoeat_place_reports', function (Blueprint $table): void {
            $table->string('branch_name')->nullable()->after('branch_id');
        });
    }
};
