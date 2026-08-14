<?php

declare(strict_types=1);

namespace App\Nova\FieldOverrides\Repeaters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\FieldCollection;
use Laravel\Nova\Fields\Repeater as NovaRepeater;
use Laravel\Nova\Fields\Repeater\RepeatableCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Support\Fluent;

class HasMany extends NovaRepeater\Presets\HasMany
{
    public function get(NovaRequest $request, $model, string $attribute, RepeatableCollection $repeatables): Collection
    {
        if ( ! $model instanceof Model) {
            return collect();
        }

        return parent::get($request, $model, $attribute, $repeatables);
    }

    /**
     * Save the field value to permanent storage.
     *
     * @param  Model  $model
     */
    public function set(
        NovaRequest $request,
        string $requestAttribute,
        $model,
        string $attribute,
        RepeatableCollection $repeatables,
        string|int|null $uniqueField
    ): callable {
        return function () use ($request, $requestAttribute, $model, $attribute, $repeatables): void {
            $this->fixNestedRepeaterKeys($request);

            $repeaterItems = collect($request->input($requestAttribute));

            $model->{$attribute}()->delete();

            $repeaterItems->transform(static function ($item, $blockKey) use ($request, $requestAttribute, $repeatables) {
                $block = $repeatables->findByKey($item['type']);
                $fields = FieldCollection::make($block->fields($request));
                $data = Fluent::make();

                [$regularFields, $nestedFields] = $fields
                    ->withoutUnfillable()
                    ->withoutMissingValues()
                    ->partition(fn ($field) => ! ($field instanceof NovaRepeater));

                $callbacks = $regularFields
                    ->map(static fn (Field $field) => $field->fillInto($request, $data, $field->attribute, "{$requestAttribute}.{$blockKey}.fields.{$field->attribute}"))
                    ->filter(static fn ($callback) => is_callable($callback))
                    ->toBase();

                return [$data, $callbacks, $nestedFields, $blockKey];
            })->each(function ($tuple) use ($model, $attribute, $request, $requestAttribute): void {
                [$data, $callbacks, $nestedFields, $blockKey] = $tuple;

                $createdModel = $model->{$attribute}()->forceCreate($data->getAttributes());

                $callbacks->each->__invoke();

                $nestedFields->each(function ($field) use ($request, $requestAttribute, $blockKey, $createdModel): void {
                    $nestedRequestAttr = "{$requestAttribute}.{$blockKey}.fields.{$field->attribute}";
                    $field->getPreset()->set($request, $nestedRequestAttr, $createdModel, $field->attribute, $field->repeatables, null)();
                });
            });
        };
    }

    /**
     * Nova's JS Repeater field wraps all field names in [fields][key], but when a nested repeater's
     * fill() runs first, its keys already contain bracket notation (e.g. items[0][fields][title]).
     * PHP stops at the first ] when parsing, so groups[0][fields][items[0][type]] becomes the key
     * "items[0" instead of a proper nested array. We recursively walk the entire request and reconstruct
     * those malformed keys at any depth before deferring to the parent set().
     */
    protected function fixNestedRepeaterKeys(NovaRequest $request): void
    {
        $request->replace($this->reconstructMalformedKeys($request->all()));
    }

    protected function reconstructMalformedKeys(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->reconstructMalformedKeys($value);
            }

            if (is_string($key) && preg_match('/^(\w+)\[(\d+)$/', $key, $matches)) {
                $result[$matches[1]][(int) $matches[2]] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
