<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wheretoeat_place_reports', function (Blueprint $table) {
           $table->boolean('ignored')->default(false)->after('completed');
        });
    }
};
