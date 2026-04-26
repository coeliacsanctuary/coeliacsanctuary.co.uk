<?php

declare(strict_types=1);

namespace Jpeters8889\PreviewButton;

use Laravel\Nova\Fields\Field;
use Spatie\MediaLibrary\HasMedia;

class PreviewButton extends Field
{
    public $component = 'preview-button';

    public function forModel(string $model): static
    {
        return $this->withMeta(['model' => $model]);
    }

    public function resolve($resource, ?string $attribute = null): void
    {
        parent::resolve($resource, $attribute);

        if ($resource instanceof HasMedia) {
            $primaryMedia = $resource->getFirstMedia('primary');
            $socialMedia = $resource->getFirstMedia('social');

            $this->withMeta([
                'primary_image_url' => $primaryMedia?->getUrl('webp') ?? $primaryMedia?->getUrl(),
                'social_image_url' => $socialMedia?->getUrl(),
            ]);
        }
    }
}
