<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Blogs\Blog;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Tools\Request;
use Stringable;

class ViewBlogsForBlogTagTool extends BaseTool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'View the blogs tagged with one or more given tag id, returned by newest first.';
    }

    /**
     * Execute the tool's logic.
     */
    protected function execute(Request $request): Stringable|string
    {
        return Blog::query()
            ->whereRelation('tags', fn (Builder $builder) => $builder->whereIn('id', $request->array('tag_id')))
            ->orderByDesc('created_at')
            ->with(['tags'])
            ->get()
            ->map(fn (Blog $blog) => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'link' => $blog->absoluteLink(),
                'short_description' => $blog->meta_description,
                'long_description' => $blog->description,
                'tags' => $blog->tags->pluck('tag')->toArray(),
                'created' => $blog->created_at,
                'updated' => $blog->updated_at,
            ])
            ->toJson();
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'tag_id' => $schema->array()->items($schema->integer())->required(),
        ];
    }
}
