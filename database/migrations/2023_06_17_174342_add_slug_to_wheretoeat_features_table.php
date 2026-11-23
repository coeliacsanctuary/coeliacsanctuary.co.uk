<?php

declare(strict_types=1);

use App\Models\EatingOut\EateryFeature;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('wheretoeat_features', function (Blueprint $table): void {
            $table->dropColumn('icon');
        });

        Schema::table('wheretoeat_features', function (Blueprint $table): void {
            $table->string('slug')->after('feature')->index();
        });

        EateryFeature::query()
            ->get()
            ->each(fn (EateryFeature $feature) => $feature->update(['slug' => Str::slug($feature->feature)]));
    }
};
