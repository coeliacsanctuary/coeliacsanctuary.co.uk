<?php

declare(strict_types=1);

namespace Jpeters8889\CollectionItemSearch;

use App\Models\Collections\CollectionGroupItem;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class CollectionItemSearch extends Field
{
    /** @var string */
    public $component = 'collection-item-search';

    public function resolve($resource, $attribute = null): void
    {
        parent::resolve($resource, $attribute);

        if ( ! ($resource instanceof CollectionGroupItem) || ! $resource->item_type || ! $resource->item_id) {
            return;
        }

        $resource->loadMissing('item');

        $item = $resource->item;

        if ( ! $item) {
            return;
        }

        $this->withMeta(['selected_item' => SearchResult::fromModel($item, $resource->item_type)]);
    }

    public function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute): void
    {
        if ( ! $request->exists($requestAttribute)) {
            return;
        }

        $value = $request[$requestAttribute];
        $model->{$attribute} = ($value !== '' && $value !== null) ? (int) $value : null;
    }
}
