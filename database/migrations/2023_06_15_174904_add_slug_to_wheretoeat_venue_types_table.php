<?php

declare(strict_types=1);

use App\Models\EatingOut\EateryVenueType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('wheretoeat_venue_types', function (Blueprint $table): void {
            $table->string('slug')->after('venue_type')->index();
        });

        EateryVenueType::query()
            ->get()
            ->each(fn (EateryVenueType $venueType) => $venueType->update(['slug' => Str::slug($venueType->venue_type)]));
    }
};
