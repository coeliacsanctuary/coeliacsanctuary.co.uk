<?php

declare(strict_types=1);

namespace App\Support\NovaPreview;

use Illuminate\Support\Str;

class BlogRenderer extends Renderer
{
    public function component(): string
    {
        return 'Blog/Preview';
    }

    public function payload(array $data): array
    {
        $twitterEmbedScripts = [
            '<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>',
            '<script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>',
        ];

        $bodyImages = $data['body_images'] ?? [];

        $body = Str::of($data['body'] ?? '')
            ->replace($twitterEmbedScripts, '', false)
            ->replace('&quot;', '"')
            ->markdown(['renderer' => ['soft_break' => '<br />']]);

        $bodyString = $body->toString();

        foreach ($bodyImages as $image) {
            if (empty($image['url'])) {
                continue;
            }

            $bodyString = str_replace($image['file_name'], $image['url'], $bodyString);
        }

        return [
            'blog' => [
                'id' => 0,
                'title' => Str::of($data['title'] ?? '')->replace('&quot;', '"')->toString(),
                'image' => $data['primary_image_url'] ?? '',
                'published' => now()->format('jS F Y'),
                'updated' => null,
                'description' => $data['description'] ?? '',
                'body' => $bodyString,
                'hasTwitterEmbed' => Str::contains($data['body'] ?? '', $twitterEmbedScripts),
                'show_author' => (bool) ($data['show_author'] ?? true),
                'tags' => [],
                'featured_in' => [],
                'body_images' => $data['body_images'] ?? [],
            ],
        ];
    }
}
