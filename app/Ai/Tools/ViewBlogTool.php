<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Blogs\Blog;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class ViewBlogTool extends BaseTool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return <<<'text'
        View a blog with the given id.

        Do not just return the blog content like for like, just whatever key facts or info or snippets required, with a link to the full blog.

        The goal is to always drive the user around the website to other pages.
        text;
    }

    /**
     * Execute the tool's logic.
     */
    protected function execute(Request $request): Stringable|string
    {
        $blog = Blog::query()->findOrFail($request->integer('blog_id'));

        $data = [
            'title' => $blog->title,
            'slug' => $blog->slug,
            'link' => $blog->absoluteLink(),
            'short_description' => $blog->meta_description,
            'long_description' => $blog->description,
            'tags' => $blog->tags->pluck('tag')->toArray(),
            'content' => $blog->body,
            'created' => $blog->created_at,
            'updated' => $blog->updated_at,
        ];

        return (string) json_encode($data);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'blog_id' => $schema->integer()->required(),
        ];
    }
}
