<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use DOMDocument;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use LibXMLError;

class ValidArticleHtmlRule implements ValidationRule
{
    /**
     * @var array<int, string>
     */
    protected array $allowedCustomTags = ['article-header', 'article-image', 'article-iframe', 'article-button'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;

        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML("<div>{$value}</div>");

        $xmlErrors = collect(libxml_get_errors())
            ->map(fn (LibXMLError $error) => mb_trim($error->message))
            ->reject(fn (string $error) => collect($this->allowedCustomTags)->contains(fn (string $tag) => Str::contains(mb_strtolower($error), $tag)))
            ->reject(fn (string $error) => Str::contains($error, 'htmlParseEntityRef: no name'))
            ->reject(fn (string $error) => Str::contains($error, 'htmlParseEntityRef: expecting \';'))
            ->values();

        libxml_clear_errors();

        if ($xmlErrors->isNotEmpty()) {
            $fail($xmlErrors->first());

            return;
        }

        if (Str::contains($value, '<iframe')) {
            $fail('Use <article-iframe> instead of <iframe>');

            return;
        }

        preg_match_all(
            '/<([a-zA-Z][a-zA-Z0-9\-]*)\b[^>]*>(.*?)<\/([a-zA-Z][a-zA-Z0-9\-]*)>/s',
            $value,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            [, $open, , $close] = $match;

            if (strcasecmp($open, $close) === 0 && $open !== $close) {
                $fail("Mismatched tag casing detected: <{$open}>...</{$close}>. Tag names must match exactly.");

                return;
            }
        }
    }
}
