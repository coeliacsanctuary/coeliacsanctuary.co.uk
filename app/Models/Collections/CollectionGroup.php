<?php

declare(strict_types=1);

namespace App\Models\Collections;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class CollectionGroup extends Model implements Sortable
{
    use SortableTrait;

    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
        'sort_on_has_many' => true,
    ];

    /** @return BelongsTo<Collection, $this> */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /** @return HasMany<CollectionGroupItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(CollectionGroupItem::class)->orderBy('position');
    }

    /** @return Builder<static> */
    public function buildSortQuery(): Builder
    {
        return static::query()->where('collection_id', $this->collection_id);
    }
}
