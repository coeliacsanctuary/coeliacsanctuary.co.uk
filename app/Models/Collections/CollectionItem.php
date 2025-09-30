<?php

declare(strict_types=1);

namespace App\Models\Collections;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @property string $title
 * @property string $link
 * @property string $main_image
 * @property string $square_image
 * @property string $meta_description
 * @property Carbon $lastUpdated
 */
class CollectionItem extends Model implements Sortable
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

    /** @return MorphTo<Model, $this> */
    public function item(): MorphTo
    {
        return $this->morphTo('item');
    }

    /** @return Builder<static> */
    public function buildSortQuery(): Builder
    {
        return static::query()->where('collection_id', $this->collection_id);
    }
}
