<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportBlogTagsCsvCommand extends Command
{
    protected $signature = 'one-time:coeliac:import-blog-tags-csv {--test}';

    public function handle(): void
    {
        $previousBlogs = Blog::query()
            ->where('id', '>', 304)
            ->with('tags')
            ->get()
            ->map(fn (Blog $blog) => [
                $blog->id,
                ...$blog->tags->pluck('tag')->toArray(),
            ]);

        if ($this->option('test') === false) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            DB::table('blog_tags')->truncate();
            DB::table('blog_assigned_tags')->truncate();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        Str::of(File::get(storage_path('app/migration/blog-tags.csv')))
            ->explode(PHP_EOL)
            ->filter()
            ->map(fn (string $line) => str_getcsv($line))
            ->when(true, function (Collection $rows) use ($previousBlogs): void {
                $previousBlogs->each(function (array $row) use ($rows): void {
                    /** @phpstan-ignore-next-line  */
                    $rows->prepend($row);
                });
            })
            ->lazy()
            ->each(function (array $row): void {
                $blogId = array_shift($row);
                $tags = collect($row)->unique()->filter();

                if ($this->option('test')) {
                    $this->info("Found blog ID: {$blogId}, tags: {$tags->join(', ')}");

                    return;
                }

                $tags = $tags->map(fn (string $tag) => BlogTag::query()->firstOrCreate(['tag' => $tag]));
                $blog = Blog::query()->findOrFail($blogId);

                $blog->tags()->attach($tags->pluck('id'));
                $blog->refresh();

                $this->info("Imported {$blog->tags->count()} tags to blog #{$blog->id} - {$blog->title}");
            });

        if ( ! $this->option('test')) {
            $this->info('Done, imported ' . BlogTag::query()->count() . ' unique tags');
        }
    }
}
