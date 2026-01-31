<?php

declare(strict_types=1);

namespace App\Nova;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource as NovaResource;

/**
 * @template TModel of Model
 *
 * @mixin TModel
 *
 * @method mixed getKey()
 *
 * @extends NovaResource<TModel>
 */
abstract class Resource extends NovaResource
{
    /** @var Collection<int, Field> | null */
    protected static ?Collection $deferrableFields = null;

    public static $clickAction = 'edit';

    protected static array $createdRelations = [];

    /** @param resource<TModel> $resource */
    public static function redirectAfterUpdate(NovaRequest $request, $resource)
    {
        return '/resources/' . static::uriKey();
    }

    /** @param resource<TModel> $resource */
    public static function redirectAfterCreate(NovaRequest $request, $resource)
    {
        return '/resources/' . static::uriKey();
    }

    public function authorizedToReplicate(Request $request)
    {
        return false;
    }

    public function authorizedToView(Request $request)
    {
        return false;
    }

    public static function fill(NovaRequest $request, $model): array
    {
        self::$deferrableFields = new Collection();

        $model::saved(function ($model) use ($request): void {
            self::$deferrableFields->each(function (Field $field) use ($model, $request): void {
                if (str_contains($field->attribute, '.')) {
                    $currentModel = $model;

                    $bits = explode('.', $field->attribute);

                    foreach ($bits as $index => $bit) {
                        if ($index + 1 === count($bits)) {
                            $field->fillInto($request, $currentModel, $bit, str_replace('.', '_', $field->attribute));

                            continue;
                        }

                        if (isset(self::$createdRelations[$bit])) {
                            $currentModel = self::$createdRelations[$bit];
                        } else {
                            $currentModel = $currentModel->{$bit}()->make(Arr::get(self::relationDefaults($model), $bit, []));

                            self::$createdRelations[$bit] = $currentModel;
                        }
                    }
                }

                $field->fillInto($request, $model, $field->attribute, str_replace('.', '_', $field->attribute));
            });

            foreach (self::$createdRelations as $relation) {
                $relation->save();
            }

            self::deferredRelationAfterSave();
        });

        return parent::fill($request, $model);
    }

    public static function fillForUpdate(NovaRequest $request, $model): array
    {
        $parent = parent::fillForUpdate($request, $model);

        $model::saved(function ($model) use ($request): void {
            self::$deferrableFields->each(function (Field $field) use ($model, $request): void {
                if (str_contains($field->attribute, '.')) {
                    $currentModel = $model;

                    $bits = explode('.', $field->attribute);

                    foreach ($bits as $index => $bit) {
                        if ($index + 1 === count($bits)) {
                            $field->fillInto($request, $currentModel, $bit, str_replace('.', '_', $field->attribute));
                            $currentModel->save();

                            continue;
                        }

                        if (is_numeric($bit)) {
                            $currentModel = $currentModel[$bit];
                        } else {
                            $currentModel = $currentModel->{$bit};
                        }
                    }
                }

                $field->fillInto($request, $model, $field->attribute, str_replace('.', '_', $field->attribute));
            });
        });

        return $parent;
    }

    protected static function fillFields(NovaRequest $request, $model, $fields): array
    {
        if ( ! self::$deferrableFields || self::$deferrableFields->isEmpty()) {
            self::$deferrableFields = $fields->filter(fn ($field) => property_exists($field, 'deferrable') && $field->deferrable);
        }

        $fields = $fields->reject(fn ($field) => property_exists($field, 'deferrable') && $field->deferrable);

        return parent::fillFields($request, $model, $fields);
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    protected static function relationDefaults(Model $model): array
    {
        return [
            'variants' => [],
            'prices' => [
                'purchasable_type' => $model::class,
                'purchasable_id' => $model->id,
                'start_at' => now(),
            ],
        ];
    }

    protected static function deferredRelationAfterSave(): void
    {
        //
    }
}
