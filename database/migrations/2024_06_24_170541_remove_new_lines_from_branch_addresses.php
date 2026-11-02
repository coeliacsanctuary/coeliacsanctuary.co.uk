<?php

declare(strict_types=1);

use App\Models\EatingOut\NationwideBranch;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        NationwideBranch::query()->get()->each(function (NationwideBranch $branch): void {
            $branch->timestamps = false;
            $branch->address = str_replace('<br />', "\n", $branch->address);

            $branch->saveQuietly();
        });
    }
};
