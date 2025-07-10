<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('wheretoeat_sealiac_overview', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('wheretoeat_id')->index();
            $table->unsignedBigInteger('nationwide_branch_id')->nullable()->index();
            $table->text('overview');
            $table->unsignedInteger('thumbs_up')->default(0);
            $table->unsignedInteger('thumbs_down')->default(0);
            $table->timestamps();
        });
    }
};
