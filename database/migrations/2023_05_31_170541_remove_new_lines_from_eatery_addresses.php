<?php

declare(strict_types=1);

use App\Models\EatingOut\Eatery;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        Eatery::query()->get()->each(function (Eatery $eatery): void {
            $eatery->timestamps = false;
            $eatery->address = str_replace('<br />', "\n", $eatery->address);

            $eatery->saveQuietly();
        });
    }
};
