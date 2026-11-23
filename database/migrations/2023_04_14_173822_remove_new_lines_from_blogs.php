<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use App\Models\Blogs\Blog;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        Blog::query()->get()->each(function (Blog $blog): void {
            $blog->timestamps = false;
            $blog->body = str_replace(['<br/>', '<br />', '<br  />'], "\n", $blog->body);
            $blog->body = Str::replace('<Strong>', '<strong>', $blog->body, true);

            $blog->saveQuietly();
        });
    }
};
