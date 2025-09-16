<?php

declare(strict_types=1);

namespace App\Nova;

use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
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
/**
 * @codeCoverageIgnore
 */
abstract class Resource extends NovaResource
{
    /** @var Collection<int, Field> */
    protected static Collection $deferrableFields;

    public static $clickAction = 'edit';

    protected static $createdRelations = [];

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
        self::$deferrableFields = new Collection();

        $model::saved(function ($model) use ($request): void {
            self::$deferrableFields->each(function (Field $field) use ($model, $request): void {
                $field->fillInto($request, $model, $field->attribute, str_replace('.', '_', $field->attribute));
            });
        });

        return parent::fill($request, $model);
    }

    protected static function fillFields(NovaRequest $request, $model, $fields): array
    {
        /** @phpstan-ignore-next-line  */
        self::$deferrableFields = $fields->filter(fn ($field) => property_exists($field, 'deferrable') && $field->deferrable);

        $fields = $fields->reject(fn ($field) => property_exists($field, 'deferrable') && $field->deferrable);

        return parent::fillFields($request, $model, $fields);
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    protected static function relationDefaults($model): array
    {
        return [
            'variants' => [],
            'prices' => [
                'product_id' => match (true) {
                    $model instanceof ShopProduct => $model->id,
                    $model instanceof ShopProductVariant => $model->product_id,
                    default => null,
                },
                'start_at' => now(),
            ],
        ];
    }

    protected static function deferredRelationAfterSave(): void
    {
        if (isset(self::$createdRelations['prices'], self::$createdRelations['variants'])) {
            self::$createdRelations['prices']->update([
                'variant_id' => self::$createdRelations['variants']->id,
            ]);
        }
    }
}
