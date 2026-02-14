<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Blogs\BlogTag;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchBlogTagsTool extends BaseTool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search for blog tags by term';
    }

    /**
     * Execute the tool's logic.
     */
    protected function execute(Request $request): Stringable|string
    {
        return BlogTag::query()
            ->whereLike('tag', "%{$request->string('term')->toString()}%")
            ->get()
            ->map(fn (BlogTag $tag) => [
                'id' => $tag->id,
                'tag' => $tag->tag,
                'slug' => $tag->slug,
                'link' => config('app.url') . '/' . route('blog.index.tags', $tag),
            ])
            ->toJson();
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'term' => $schema->string()->required(),
        ];
    }
}
