<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Jpeters8889\ApexCharts\Chartable;
use Jpeters8889\ApexCharts\DTO\DateRange;

/*
|--------------------------------------------------------------------------
| Card API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your card. These routes
| are loaded by the ServiceProvider of your card. You're free to add
| as many additional routes to this file as your card may require.
|
*/

Route::get('/', function (Request $request): array {
    $request->validate([
        'chartable' => ['required', 'string'],
        'selectedDateRange' => ['string'],
        'startDate' => ['required_if:selectedDateRange,custom', 'date', 'date_format:Y-m-d', 'before:endDate'],
        'endDate' => ['required_if:selectedDateRange,custom', 'date', 'date_format:Y-m-d', 'after:startDate'],
    ]);

    /** @var Chartable $chartable */
    $chartable = app($request->string('chartable')->toString());

    $dateRanges = $chartable->dateRanges();

    /** @var DateRange $selectedDateRange */
    $selectedDateRange = Arr::get($dateRanges, $request->get('selectedDateRange', $chartable->defaultDateRange()));

    if ( ! $selectedDateRange) {
        /** @var Carbon $startDate */
        $startDate = $request->date('startDate');

        /** @var Carbon $endDate */
        $endDate = $request->date('endDate');

        $difference = $startDate->diffInDays($endDate);

        $selectedDateRange = new DateRange(
            startDate: $startDate,
            endDate: $endDate,
            unit: match (true) {
                $difference <= 2 => 'hour',
                $difference <= 60 => 'day',
                default => 'month',
            },
            dateFormat: match (true) {
                $difference <= 2 => 'D ga',
                $difference <= 60 => 'd/m',
                default => 'M/y',
            },
            label: 'Custom',
        );
    }

    return [
        'dates' => collect($dateRanges)->map(fn (DateRange $dateRange, string $key) => ['value' => $key, 'label' => $dateRange->label])->values(),
        'selectedDateRange' => $request->get('selectedDateRange', $chartable->defaultDateRange()),
        'chart' => $chartable->render($selectedDateRange),
    ];
});
