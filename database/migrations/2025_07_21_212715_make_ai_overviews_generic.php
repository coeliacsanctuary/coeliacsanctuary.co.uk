<?php

declare(strict_types=1);

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('wheretoeat_sealiac_overview', function (Blueprint $table): void {
            $table->string('model_type')->after('id');
            $table->unsignedBigInteger('model_id')->after('id');

            $table->index(['model_type', 'model_id']);

            $table->rename('sealiac_overviews');
        });

        DB::table('sealiac_overviews')
            ->whereNotNull('nationwide_branch_id')
            ->update([
                'model_type' => NationwideBranch::class,
                'model_id' => DB::raw('nationwide_branch_id'),
            ]);

        DB::table('sealiac_overviews')
            ->whereNull('nationwide_branch_id')
            ->update([
                'model_type' => Eatery::class,
                'model_id' => DB::raw('wheretoeat_id'),
            ]);

        Schema::table('sealiac_overviews', function (Blueprint $table): void {
            $table->dropColumn(['wheretoeat_id', 'nationwide_branch_id']);
        });
    }
};
