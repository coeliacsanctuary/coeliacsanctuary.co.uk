<?php

declare(strict_types=1);

namespace App\DataObjects\Shop;

final readonly class WalletCardSection
{
    public function __construct(
        public string $key,
        public string $label,
        public string $value,
    ) {
        //
    }

    public function appendedWith(self $section): self
    {
        return new self($this->key, $this->label, "{$this->value}\n\n{$section->value}");
    }

    public function withLabel(string $label): self
    {
        return new self($this->key, $label, $this->value);
    }
}
