<?php

declare(strict_types=1);

namespace App\Support\Shop\Wallet;

use Spatie\LaravelMobilePass\Builders\Google\Entities\Image;
use Spatie\LaravelMobilePass\Builders\Google\GenericPassBuilder as BaseGenericPassBuilder;
use Spatie\LaravelMobilePass\Builders\Google\Validators\GooglePassObjectValidator;

class GenericPassBuilder extends BaseGenericPassBuilder
{
    protected ?string $backgroundColor = null;

    protected ?Image $logo = null;

    protected ?Image $hero = null;

    protected static function validator(): GooglePassObjectValidator
    {
        return new GenericObjectValidator();
    }

    public function setBackgroundColor(string $hex): self
    {
        $this->backgroundColor = $hex;

        return $this;
    }

    public function setLogoUrl(string $url): self
    {
        $this->logo = Image::fromUrl($url);

        return $this;
    }

    public function setHeroImageUrl(string $url): self
    {
        $this->hero = Image::fromUrl($url);

        return $this;
    }

    /** @return array<string, mixed> */
    protected function compileData(): array
    {
        return array_merge(parent::compileData(), $this->filterEmpty([
            'hexBackgroundColor' => $this->backgroundColor,
            'logo' => $this->logo?->toArray(),
            'heroImage' => $this->hero?->toArray(),
        ]));
    }
}
