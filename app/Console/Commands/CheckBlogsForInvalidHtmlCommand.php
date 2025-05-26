<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Blogs\Blog;
use DOMDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Prompts\Progress;
use LibXMLError;

use function Laravel\Prompts\progress;
use function Laravel\Prompts\table;

class CheckBlogsForInvalidHtmlCommand extends Command
{
    protected $signature = 'one-time:coeliac:check-blogs-for-invalid-html {--stop-on-errors=false}';

    protected array $errors = [];

    public function handle(): void
    {
        progress(
            'Checking blogs for invalid HTML',
            Blog::query()->get(),
            function (Blog $blog, Progress $progress): void {
                $progress->label("Checking #{$blog->id} {$blog->title}");

                try {
                    $this->checkBlog($blog);
                } catch (ValidationException $e) {
                    if ($this->option('stop-on-errors') !== 'false') {
                        throw $e;
                    }

                    $this->errors[] = [
                        'id' => $blog->id,
                        'title' => $blog->title,
                        'errors' => $e->errors(),
                    ];
                }
            }
        );

        info('Found ' . count($this->errors) . ' blogs with issues');

        foreach ($this->errors as $error) {
            table(["#{$error['id']} - {$error['title']}"], $error['errors']);
        }
    }

    protected function checkBlog(Blog $blog): void
    {
        $errors = [];

        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $allowedCustomTags = ['article-header', 'article-image', 'article-iframe'];
        $dom->loadHTML("<div>{$blog->body}</div>");

        $xmlErrors = collect(libxml_get_errors())
            ->map(fn (LibXMLError $error) => $error->message)
            ->reject(fn (string $error) => collect($allowedCustomTags)->filter(fn (string $tag) => Str::contains(mb_strtolower($error), $tag))->isNotEmpty())
            ->reject(fn (string $error) => Str::contains($error, 'htmlParseEntityRef: no name'))
            ->reject(fn (string $error) => Str::contains($error, 'htmlParseEntityRef: expecting \';'))
            ->toArray();

        libxml_clear_errors();

        if ( ! empty($xmlErrors)) {
            $errors = [...$xmlErrors];
        }

        if (Str::contains($blog->body, '<iframe')) {
            $errors[] = 'Blog contains an iframe';
        }

        preg_match_all(
            '/<([a-zA-Z][a-zA-Z0-9\-]*)\b[^>]*>(.*?)<\/([a-zA-Z][a-zA-Z0-9\-]*)>/s',
            $blog->body,
            $matches,
            PREG_SET_ORDER
        );

        collect($matches)->each(function ($match) use (&$errors): void {
            [, $open, , $close] = $match;

            if (strcasecmp($open, $close) === 0 && $open !== $close) {
                $errors[] = "Mismatched tag casing detected: <{$open}>...</{$close}>. Tag names must match exactly.";
            }
        });

        if (count($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }
}
