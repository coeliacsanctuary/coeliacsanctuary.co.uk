<?php

declare(strict_types=1);

use App\Models\Blogs\Blog;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Blog::query()->get()->each(function (Blog $blog): void {
            $blog->timestamps = false;
            $blog->body = str_replace(['<br/>', '<br />', '<br  />'], "\n", $blog->body);
            $blog->body = Illuminate\Support\Str::replace('<Strong>', '<strong>', $blog->body, true);

            $blog->saveQuietly();
        });
    }
};
