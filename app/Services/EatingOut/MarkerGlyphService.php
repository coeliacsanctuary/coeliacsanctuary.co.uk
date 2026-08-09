<?php

declare(strict_types=1);

namespace App\Services\EatingOut;

use App\Enums\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use Illuminate\Contracts\View\Factory;

class MarkerGlyphService
{
    public function __construct(protected Factory $viewFactory)
    {
        //
    }

    public function resolve(int $typeId, ?int $venueTypeId = null): string
    {
        return $this->venueTypeGlyph($venueTypeId) ?? $this->genericGlyph($typeId);
    }

    protected function venueTypeGlyph(?int $venueTypeId): ?string
    {
        if ($venueTypeId === null) {
            return null;
        }

        $slug = EateryVenueType::query()->whereKey($venueTypeId)->value('slug');

        if ( ! is_string($slug) || $slug === '') {
            return null;
        }

        $view = "markers.glyphs.{$slug}";

        return $this->viewFactory->exists($view) ? $view : null;
    }

    protected function genericGlyph(int $typeId): string
    {
        $type = match (EateryType::tryFrom($typeId)) {
            EateryType::HOTEL => 'hotel',
            EateryType::ATTRACTION => 'attraction',
            default => 'eatery',
        };

        return "markers.glyphs.generic.{$type}";
    }
}
