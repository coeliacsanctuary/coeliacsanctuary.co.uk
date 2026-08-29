<?php

declare(strict_types=1);

use App\Http\Controllers\Api\EatingOut\Browse\IndexController as BrowseIndexController;
use App\Http\Controllers\Api\EatingOut\Browse\Search\StoreController as BrowseSearchStoreController;
use App\Http\Controllers\Api\EatingOut\CheckRecommendedPlace\GetController as CheckRecommendedPlaceGetController;
use App\Http\Controllers\Api\EatingOut\Details\Branches\IndexController as DetailsBranchesIndexController;
use App\Http\Controllers\Api\EatingOut\Details\ShowController as DetailsShowController;
use App\Http\Controllers\Api\EatingOut\Features\IndexController as FeatureIndexController;
use App\Http\Controllers\Api\EatingOut\Lookup\GetController as WhereToEatLookupGetController;
use App\Http\Controllers\Api\EatingOut\Lookup\IndexController as WhereToEatLookupIndexController;
use App\Http\Controllers\Api\EatingOut\Marker\GetController as MarkerGetController;
use App\Http\Controllers\Api\EatingOut\Random\ShowController as RandomShowController;
use App\Http\Controllers\Api\EatingOut\ReviewImages\StoreController as ReviewImagesStoreController;
use App\Http\Controllers\Api\EatingOut\SealiacOverview\GetController as EaterySealiacOverviewGetController;
use App\Http\Controllers\Api\EatingOut\SuggestEdits\IndexController as SuggestEditsIndexController;
use App\Http\Controllers\Api\EatingOut\SuggestEdits\StoreController as SuggestEditsStoreController;
use Illuminate\Support\Facades\Route;

Route::post('lookup', WhereToEatLookupIndexController::class)->name('api.wheretoeat.lookup.index');
Route::get('lookup/{id}', WhereToEatLookupGetController::class)->name('api.wheretoeat.lookup.get');

Route::post('review/image-upload', ReviewImagesStoreController::class)
    ->name('api.wheretoeat.review.image-upload');

Route::get('features', FeatureIndexController::class)->name('api.wheretoeat.features');

Route::get('browse', BrowseIndexController::class)->name('api.wheretoeat.browse');

Route::post('browse/search', BrowseSearchStoreController::class)->name('api.wheretoeat.browse.search');

Route::post('check-recommended-place', CheckRecommendedPlaceGetController::class)->name('api.wheretoeat.check-recommended-place');

Route::get('random', RandomShowController::class)->name('api.wheretoeat.random');

Route::get('marker/{typeId}/{venueTypeId?}', MarkerGetController::class)
    ->whereNumber(['typeId', 'venueTypeId'])
    ->name('api.wheretoeat.marker.get');

Route::get('{eatery}', DetailsShowController::class)->name('api.wheretoeat.get');

Route::post('{eatery}/branches', DetailsBranchesIndexController::class)->name('api.wheretoeat.branches.index');

Route::get('{eatery}/suggest-edit', SuggestEditsIndexController::class)->name('api.wheretoeat.suggest-edit.get');

Route::post('{eatery}/suggest-edit', SuggestEditsStoreController::class)->name('api.wheretoeat.suggest-edit.store');

Route::get('{eatery}/sealiac', EaterySealiacOverviewGetController::class)->name('api.wheretoeat.sealiac.get');
