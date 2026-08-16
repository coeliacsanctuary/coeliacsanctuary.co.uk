<?php

declare(strict_types=1);

namespace App\Support\NovaPreview;

use App\Actions\Blogs\FindRelatedBlogsAction;
use App\Concerns\FormatsMarkdown;
use App\Models\Blogs\BlogTag;
use App\Resources\Blogs\RelatedBlogSimpleCardViewResource;
use App\Support\Helpers;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class BlogRenderer extends Renderer
{
    use FormatsMarkdown;

    /** @var array<int, string> */
    protected array $twitterEmbedScripts = [
        '<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>',
        '<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>',
    ];

    public function __construct(protected FindRelatedBlogsAction $findRelatedBlogsAction)
    {
    }

    public function component(): string
    {
        return 'Blog/Preview';
    }

    /**
     * @param  array{
     *     title?: string,
     *     short_title?: string|null,
     *     description?: string,
     *     body?: string,
     *     primary_image_url?: string,
     *     header_image_alt_text?: string|null,
     *     show_author?: bool|null,
     *     tags?: array<int, string>,
     *     primary_tag_id?: string|null,
     *     faqs?: array<int, array{question: string, answer: string|null}>,
     *     faq_display?: string|null,
     *     body_images?: array<int, array{file_name: string, url: string|null}>,
     * }  $data
     * @return array<string, mixed>
     */
    public function payload(array $data): array
    {
        $body = $data['body'] ?? '';

        $tagNames = collect($data['tags'] ?? [])->filter()->values();

        $tags = BlogTag::query()->whereIn('tag', $tagNames->all())->get();

        $faqs = collect($data['faqs'] ?? [])->filter(fn (array $faq): bool => filled($faq['question']));

        return [
            'blog' => [
                'id' => 0,
                'title' => Str::of($data['title'] ?? '')->replace('&quot;', '"')->toString(),
                'short_title' => $data['short_title'] ?? null,
                'image' => $data['primary_image_url'] ?? '',
                'header_image_alt_text' => $data['header_image_alt_text'] ?? null,
                'published' => now()->diffForHumans(),
                'updated' => null,
                'description' => $data['description'] ?? '',
                'body' => $this->renderBody($body, $data['body_images'] ?? []),
                'reading_time' => Helpers::readingTime($body),
                'comments_count' => 0,
                'hasTwitterEmbed' => Str::contains($body, $this->twitterEmbedScripts),
                'show_author' => (bool) ($data['show_author'] ?? true),
                'tags' => $tagNames->map(fn (string $tag): array => ['tag' => $tag, 'slug' => Str::slug($tag)])->all(),
                'featured_in' => [],
                'faqs' => $faqs->isNotEmpty() ? $faqs->values()->all() : null,
                'faq_display' => $data['faq_display'] ?? null,
                'body_images' => $data['body_images'] ?? [],
            ],
            'relatedBlogs' => RelatedBlogSimpleCardViewResource::collection(
                $this->findRelatedBlogsAction->handle($tags, $this->resolvePrimaryTag($tags, $data['primary_tag_id'] ?? null))
            ),
        ];
    }

    /** @param  array<int, array{file_name?: string, url?: string|null}>  $bodyImages */
    protected function renderBody(string $body, array $bodyImages): string
    {
        $rendered = (string) $this->formatMarkdown(
            $body,
            fn (Stringable $str): Stringable => $str->replace($this->twitterEmbedScripts, '', false),
        );

        foreach ($bodyImages as $image) {
            if (empty($image['url']) || empty($image['file_name'])) {
                continue;
            }

            $rendered = str_replace($image['file_name'], $image['url'], $rendered);
        }

        return $rendered;
    }

    /** @param  Collection<int, BlogTag>  $tags */
    protected function resolvePrimaryTag(Collection $tags, ?string $primaryTag): ?BlogTag
    {
        if (blank($primaryTag)) {
            return null;
        }

        return $tags->first(fn (BlogTag $tag): bool => $tag->tag === $primaryTag);
    }
}
