<?php

declare(strict_types=1);

namespace App\Nova\Resources\Shop;

use App\Models\Shop\ShopHoliday;
use App\Nova\Resource;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;

/** @extends Resource<ShopHoliday> */
class Holidays extends Resource
{
    /** @var class-string<ShopHoliday> */
    public static string $model = ShopHoliday::class;

    public function fields(NovaRequest $request)
    {
        return [
            ID::make('id')->hide(),

            Date::make('Start Date')
                ->rules(['required'])
                ->default(today()),

            Date::make('End Date')
                ->rules(['required'])
                ->dependsOn('start_date', function (Date $field, NovaRequest $request, FormData $formData): void {
                    $startDate = $request->date('start_date');

                    $field
                        ->min($startDate)
                        ->default(fn () => $startDate);
                }),

            Date::make('Ship On')
                ->rules(['required'])
                ->dependsOn('end_date', function (Date $field, NovaRequest $request, FormData $formData): void {
                    $endDate = $request->date('end_date');

                    $field
                        ->min($endDate)
                        ->default(fn () => $endDate?->addDay());
                }),
        ];
    }

    public function title()
    {
        return $this->start_date->format('jS M Y') . ' - ' . $this->end_date->format('jS M Y');
    }
}
