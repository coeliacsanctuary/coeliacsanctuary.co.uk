<?php

declare(strict_types=1);

use App\Models\Recipes\Recipe;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        $changes = [
            'Alison Peters/Jamie Peters' => 'Alison Peters',
            'Karen Peters/Alison Wheatley' => 'Alison Wheatley',
            'Jamies Peters' => 'Jamie Peters',
            'Alison Whetley' => 'Alison Wheatley',
        ];

        foreach ($changes as $old => $new) {
            Recipe::query()->where('author', $old)->update(['author' => $new]);
        }
    }
};
