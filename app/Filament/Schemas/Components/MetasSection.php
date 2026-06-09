<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class MetasSection
{
    public static function make(false|string $metaTags = 'meta_tags', false|string $metaDescription = 'meta_description'): Section
    {
        return Section::make('Metas')
            ->collapsible()
            ->collapsed(fn (string $operation): bool => $operation !== 'create')
            ->schema(function () use ($metaTags, $metaDescription) {
                $schema = [];

                if ($metaTags) {
                    $schema[] = TextInput::make($metaTags)->required();
                }

                if ($metaDescription) {
                    $schema[] = Textarea::make($metaDescription)->required();
                }

                return $schema;
            });
    }
}
