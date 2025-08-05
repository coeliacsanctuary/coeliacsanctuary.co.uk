<?php

declare(strict_types=1);

use App\Models\Blogs\Blog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table): void {
            $table->boolean('show_author')->default(true)->after('meta_description');
        });

        $blogsToHide = [
            112, // heidi, cancer story
            121, // jess, hospital
            123, // katie, disney
            246, // sophie, tour of italy
        ];

        Blog::query()->whereIn('id', $blogsToHide)->update(['show_author' => false]);
    }
};
