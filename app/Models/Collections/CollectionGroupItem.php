<?php

declare(strict_types=1);

namespace App\Models\Collections;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class CollectionGroupItem extends Model implements Sortable
{
    use SortableTrait;

    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
        'sort_on_has_many' => true,
    ];

    protected static function booted(): void
    {
        static::saved(function (self $item): void {
            $item->group?->touch();
            $item->group?->collection?->touch();
        });
    }

    /** @return BelongsTo<CollectionGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(CollectionGroup::class, 'collection_group_id');
    }

    /** @return MorphTo<Model, $this> */
    public function item(): MorphTo
    {
        return $this->morphTo('item');
    }

    /** @return Builder<static> */
    public function buildSortQuery(): Builder
    {
        return static::query()->where('collection_group_id', $this->collection_group_id);
    }
}
