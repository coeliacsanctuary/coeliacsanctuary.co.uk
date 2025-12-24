<?php

declare(strict_types=1);

use App\Imports\WteNationwideImport;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Support\Facades\Route;
use Laravel\Nova\Http\Requests\NovaRequest;

Route::get('/', fn (NovaRequest $request) => inertia('WteNationwideBranchImport.Index', [
    'csrf' => csrf_token(),
    'errorMessage' => $request->session()->get('errors')?->first('csv'),
]));

Route::post('process', function (NovaRequest $request) {
    $request->validate(['csv' => ['required', 'mimes:csv,txt']]);

    $importer = new WteNationwideImport();

    $csv = $importer->toCollection($request->file('csv'));

    $results = $importer->collection($csv->first());

    return inertia('WteNationwideBranchImport.Processed', [
        'csrf' => csrf_token(),
        'items' => $results,
    ]);
});

Route::post('add', function (NovaRequest $request) {
    $request->validate(['collection' => 'required', 'json']);

    $items = collect(json_decode($request->input('collection'), true));

    $ids = [];
    $added = 0;
    $processed = 0;
    $errors = [];

    $items
        ->reject(fn ($item) => $item['error'])
        ->each(function ($item) use (&$added, &$processed, &$errors, &$ids): void {
            $processed++;

            try {
                if (data_get($item, 'county.id') === 'NEW') {
                    $county = EateryCounty::withoutGlobalScopes()
                        ->firstOrCreate([
                            'county' => data_get($item, 'county.name'),
                            'country_id' => data_get($item, 'country.id'),
                        ]);

                    data_set($item, 'county.id', $county->id);
                }

                if (data_get($item, 'town.id') === 'NEW') {
                    $town = EateryTown::withoutGlobalScopes()
                        ->firstOrCreate([
                            'town' => data_get($item, 'town.name'),
                            'county_id' => data_get($item, 'county.id'),
                        ]);

                    data_set($item, 'town.id', $town->id);
                }

                if (data_get($item, 'area.id') === 'NEW') {
                    $area = EateryArea::withoutGlobalScopes()
                        ->firstOrCreate([
                            'area' => data_get($item, 'area.name'),
                            'town_id' => data_get($item, 'town.id'),
                        ]);

                    data_set($item, 'area.id', $area->id);
                }

                $row = NationwideBranch::query()->create([
                    'wheretoeat_id' => data_get($item, 'wheretoeat_id'),
                    'name' => data_get($item, 'name'),
                    'country_id' => data_get($item, 'country.id'),
                    'county_id' => data_get($item, 'county.id'),
                    'town_id' => data_get($item, 'town.id'),
                    'area_id' => data_get($item, 'area.id'),
                    'address' => data_get($item, 'address.formatted'),
                    'lat' => data_get($item, 'lat'),
                    'lng' => data_get($item, 'lng'),
                    'live' => data_get($item, 'live'),
                ]);

                $ids[] = $row->id;

                $added++;
            } catch (Exception $exception) {
                dd($exception, $item);
                $errors[data_get($item, 'name')] = $exception->getMessage();
            }
        });

    sleep(1);

    // set slugs
    NationwideBranch::query()
        ->whereIn('id', $ids)
        ->lazy()
        ->each(function (NationwideBranch $branch): void {
            $branch->slug = $branch->generateSlug(true);
            $branch->saveQuietly();
        });

    return inertia('WteNationwideBranchImport.Complete', [
        'total' => $items->count(),
        'rejected' => $items->count() - $processed,
        'processed' => $processed,
        'added' => $added,
        'failed' => count($errors),
        'errors' => $errors,
    ]);
});
