<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs\Forms\Components;

use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use Filament\Forms\Components\TagsInput;

class BlogTagsInput extends TagsInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadStateFromRelationshipsUsing(static function (BlogTagsInput $component, ?Blog $record): void {
            if ( ! $record instanceof Blog) {
                return;
            }

            $record->load('tags');

            $component->state($record->tags->pluck('tag')->all());
        });

        $this->saveRelationshipsUsing(static function (BlogTagsInput $component, ?Blog $record, array $state): void {
            $component->syncTagsWithAnyType($record, $state);
        });

        $this->dehydrated(false);
    }

    /**
     * @param  array<string>  $state
     */
    protected function syncTagsWithAnyType(?Blog $record, array $state): void
    {
        if ( ! $record instanceof Blog) {
            return;
        }

        $tags = collect($state)
            ->map(fn (string $tagName) => BlogTag::query()->firstOrCreate(['tag' => $tagName]))
            ->flatten();

        $record->tags()->sync($tags->pluck('id'));
    }
}
