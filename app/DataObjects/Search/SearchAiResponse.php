<?php

declare(strict_types=1);

namespace App\DataObjects\Search;

use Illuminate\Support\Arr;

class SearchAiResponse
{
    public function __construct(
        public readonly int $shop,
        public readonly int $eatingOut,
        public readonly int $blogs,
        public readonly int $recipes,
        public readonly string $reasoning,
        public readonly ?string $location = null,
    ) {
        //
    }

    public static function fromResponse(array $response): self
    {
        return new self(
            Arr::getAsInt($response, 'shop'),
            Arr::getAsInt($response, 'eating-out'),
            Arr::getAsInt($response, 'blogs'),
            Arr::getAsInt($response, 'recipes'),
            Arr::getAsString($response, 'explanation'),
            Arr::getAsNullableString($response, 'location'),
        );
    }

    public function toModel(): array
    {
        return [
            'blogs' => $this->blogs,
            'recipes' => $this->recipes,
            'eateries' => $this->eatingOut,
            'shop' => $this->shop,
            'explanation' => $this->reasoning,
            'location' => $this->location,
        ];
    }
}
