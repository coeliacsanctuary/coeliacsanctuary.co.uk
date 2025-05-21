<?php

declare(strict_types=1);

namespace Jpeters8889\Body;

use Closure;
use DOMDocument;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use LibXMLError;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Body extends Field
{
    public $component = 'body';

    protected $canHaveImages = false;

    public $showOnIndex = false;

    public $showOnDetail = false;

    public $showOnCreation = true;

    public $showOnUpdate = true;

    protected bool $mustBeValidHtml;

    public function __construct($name, $attribute = null, ?callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->rows('25');
    }

    public function rules($rules)
    {
        if ( ! $this->mustBeValidHtml) {
            return parent::rules($rules);
        }

        return parent::rules([
            ...$rules,
            function (string $attribute, mixed $value, Closure $fail) {
                libxml_use_internal_errors(true);

                $dom = new DOMDocument();
                $allowedCustomTags = ['article-header', 'article-image', 'coeliac-iframe'];
                $dom->loadHTML("<div>{$value}</div>");

                $xmlErrors = collect(libxml_get_errors())
                    ->map(fn (LibXMLError $error) => $error->message)
                    ->reject(fn (string $error) => collect($allowedCustomTags)->filter(fn (string $tag) => Str::contains(mb_strtolower($error), $tag))->isNotEmpty())
                    ->reject(fn (string $error) => Str::contains($error, 'htmlParseEntityRef: no name'))
                    ->reject(fn (string $error) => Str::contains($error, 'htmlParseEntityRef: expecting \';'))
                    ->toArray();

                libxml_clear_errors();

                if ( ! empty($xmlErrors)) {
                    return $fail($xmlErrors[0]);
                }

                if (Str::contains($value, '<iframe')) {
                    return $fail('Use <coeliac-iframe> instead of <iframe>');
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
                        return $fail("Mismatched tag casing detected: <{$open}>...</{$close}>. Tag names must match exactly.");
                    }
                }

                return true;
            },
        ]);
    }

    public function rows($rows): self
    {
        return $this->withMeta(['rows' => $rows]);
    }

    protected function resolveAttribute($resource, $attribute): mixed
    {
        $rawContents = parent::resolveAttribute($resource, $attribute);

        if ( ! $rawContents) {
            return null;
        }

        if ($this->canHaveImages) {
            /** @var HasMedia $resource */
            $resource->getMedia('body')->each(function (Media $media) use (&$rawContents): void {
                $rawContents = str_replace($media->getUrl(), $media->file_name, $rawContents);
            });
        }

        return $rawContents;
    }

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute): void
    {
        $value = $request[$requestAttribute];

        if ($this->canHaveImages()) {
            /** @var HasMedia $model */
            $model->getMedia('body')->each(function (Media $media) use (&$value): void {
                $value = str_replace($media->file_name, $media->getUrl(), $value);
            });
        }

        $value = $value;

        $model->$attribute = $value;
    }

    public function canHaveImages(): self
    {
        $this->canHaveImages = true;

        return $this;
    }

    public function mustBeValidHtml(): self
    {
        $this->mustBeValidHtml = true;

        return $this;
    }
}
