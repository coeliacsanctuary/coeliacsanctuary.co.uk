<?php

declare(strict_types=1);

namespace App\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 *
 * @property string $link
 * @property string $absolute_link
 */
trait LinkableModel
{
    public function initializeLinkable(): void
    {
        $this->append('link');
    }

    /** @return Attribute<non-falsy-string, never> */
    public function link(): Attribute
    {
        return Attribute::get(fn () => '/' . $this->linkRoot() . '/' . $this->linkColumn());
    }

    /** @return Attribute<string, never> */
    public function absoluteLink(): Attribute
    {
        return Attribute::get(fn () => config('app.url') . $this->link);
    }

    protected function linkRoot(): string
    {
        return Str::of(class_basename($this))->lower()->singular()->toString();
    }

    protected function linkColumn(): string
    {
        return $this->slug;
    }
}
