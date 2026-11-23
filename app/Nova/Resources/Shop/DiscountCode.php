<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Enums\Shop\DiscountCodeType;
use App\Models\Shop\ShopDiscountCode;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\HasManyThrough;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<ShopDiscountCode> */
/**
 * @codeCoverageIgnore
 */
class DiscountCode extends Resource
{
    /** @var class-string<ShopDiscountCode> */
    public static string $model = ShopDiscountCode::class;

    public static $clickAction = 'view';

    public static $title = 'name';

    public static $search = ['id', 'name', 'code'];

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Text::make('Name')->rules(['required']),

            Text::make('Code')->rules(['required']),

            DateTime::make('Start At')
                ->min(now()->startOfDay())
                ->default(fn () => now()->startOfDay())
                ->rules(['required', 'date', 'after:startOfDay']),

            DateTime::make('End At')
                ->rules(['required', 'date', 'after:start_date'])
                ->default(fn () => now()->addWeek()->endOfDay())
                ->dependsOn('start_at', function (DateTime $field, NovaRequest $request, FormData $formData): void {
                    $startAt = $request->date('start_at');

                    $field
                        ->min($startAt)
                        ->default(fn () => $startAt->addWeek()->endOfDay());
                }),

            Select::make('Type', 'type_id')
                ->default(fn (): int => 1)
                ->rules(['required'])
                ->displayUsingLabels()
                ->options([
                    1 => 'Percentage',
                    2 => 'Money Deduction',
                ]),

            Text::make('Deduction')
                ->exceptOnForms()
                ->displayUsing(function ($value): string {
                    if ($this->type_id === DiscountCodeType::PERCENTAGE) {
                        return "{$value}%";
                    }

                    return '£' . number_format($value / 100, 2);
                }),

            Number::make('Deduction')
                ->onlyOnForms()
                ->rules(['required'])
                ->max(100)
                ->dependsOn('type_id', function (Number $field, NovaRequest $request, FormData $formData): void {
                    $request->type_id === DiscountCodeType::PERCENTAGE->value ? $field->show() : $field->hide();
                })
                ->fillUsing(function (NovaRequest $request, ShopDiscountCode $discountCode) {
                    if ($request->integer('type_id') === DiscountCodeType::MONEY->value) {
                        return false;
                    }

                    $discountCode->deduction = $request->integer('deduction');
                }),

            Currency::make('Deduction')
                ->onlyOnForms()
                ->rules(['required'])
                ->asMinorUnits()
                ->dependsOn('type_id', function (Currency $field, NovaRequest $request, FormData $formData): void {
                    $request->type_id === DiscountCodeType::PERCENTAGE->value ? $field->hide() : $field->show();
                })
                ->fillUsing(function (NovaRequest $request, ShopDiscountCode $discountCode) {
                    if ($request->integer('type_id') === DiscountCodeType::PERCENTAGE->value) {
                        return false;
                    }

                    $discountCode->deduction = $request->float('deduction') * 100;
                }),

            Currency::make('Min Spend')
                ->asMinorUnits()
                ->rules(['required'])
                ->displayUsing(fn ($value): string => $value === 0 ? 'None' : '£' . number_format($value / 100, 2))
                ->help('Enter 0 for no min spend')
                ->default(fn (): int => 0),

            ...$request->query('viaResource') !== 'orders'
                ? [
                    Number::make('Max Claims')
                        ->displayUsing(fn ($value) => $value === 0 ? 'None' : $value)
                        ->rules(['required'])
                        ->default(fn (): int => 0)
                        ->help('Enter 0 for no limit'),

                    Number::make('Claims', 'used_count')->onlyOnIndex(),

                    HasManyThrough::make('Claims', 'orders', Orders::class),
                ]
                : [],
        ];
    }

    public function authorizedToView(Request $request): bool
    {
        return true;
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return $query->withCount('used');
    }
}
