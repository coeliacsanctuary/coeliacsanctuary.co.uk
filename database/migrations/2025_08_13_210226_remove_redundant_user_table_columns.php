<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign('users_user_level_id_foreign');

            $table->dropColumn('phone');
            $table->dropColumn('user_level_id');
            $table->dropColumn('last_logged_in_at');
            $table->dropColumn('last_visited_at');
            $table->dropColumn('welcome_valid_until');
        });
    }
};
