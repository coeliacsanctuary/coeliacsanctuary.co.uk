<?php

declare(strict_types=1);

use App\Http\Controllers\Api\EatingOut\Browse\IndexController as BrowseIndexController;
use App\Http\Controllers\Api\EatingOut\Browse\Search\StoreController as BrowseSearchStoreController;
use App\Http\Controllers\Api\EatingOut\CheckRecommendedPlace\GetController as CheckRecommendedPlaceGetController;
use App\Http\Controllers\Api\EatingOut\Details\Branches\IndexController as DetailsBranchesIndexController;
use App\Http\Controllers\Api\EatingOut\Details\ShowController as DetailsShowController;
use App\Http\Controllers\Api\EatingOut\Features\IndexController as FeatureIndexController;
use App\Http\Controllers\Api\EatingOut\IndexController as WhereToEatIndexController;
use App\Http\Controllers\Api\EatingOut\Latest\IndexController as WhereToEatLatestIndexController;
use App\Http\Controllers\Api\EatingOut\LatLng\GetController as WhereToEatLatLngGetController;
use App\Http\Controllers\Api\EatingOut\Random\ShowController as RandomShowController;
use App\Http\Controllers\Api\EatingOut\Ratings\Latest\IndexController as WhereToEatRatingsLatestIndexController;
use App\Http\Controllers\Api\EatingOut\RecommendAPlace\StoreController as WhereToEatRecommendAPlaceStoreController;
use App\Http\Controllers\Api\EatingOut\Reports\StoreController as ReportStoreController;
use App\Http\Controllers\Api\EatingOut\ReviewImages\StoreController as ReviewImagesStoreController;
use App\Http\Controllers\Api\EatingOut\Reviews\StoreController as ReviewStoreController;
use App\Http\Controllers\Api\EatingOut\SealiacOverview\GetController as EaterySealiacOverviewGetController;
use App\Http\Controllers\Api\EatingOut\SuggestEdits\IndexController as SuggestEditsIndexController;
use App\Http\Controllers\Api\EatingOut\SuggestEdits\StoreController as SuggestEditsStoreController;
use App\Http\Controllers\Api\EatingOut\Summary\IndexController as WhereToEatSummaryIndexController;
use App\Http\Controllers\Api\EatingOut\VenueTypes\IndexController as WhereToEatVenueTypesIndexController;
use Illuminate\Support\Facades\Route;

Route::get('/', WhereToEatIndexController::class)->name('api.wheretoeat.index');
Route::get('latest', WhereToEatLatestIndexController::class)->name('api.wheretoeat.latest');
Route::get('summary', WhereToEatSummaryIndexController::class)->name('api.wheretoeat.summary');
Route::get('ratings/latest', WhereToEatRatingsLatestIndexController::class)->name('api.wheretoeat.ratings.latest');
Route::get('venueTypes', WhereToEatVenueTypesIndexController::class)->name('api.wheretoeat.venueTypes');
Route::post('lat-lng', WhereToEatLatLngGetController::class)->name('api.wheretoeat.lat-lng');
Route::post('recommend-a-place', WhereToEatRecommendAPlaceStoreController::class)->name('api.wheretoeat.recommend-a-place.store');

Route::post('review/image-upload', ReviewImagesStoreController::class)
    ->name('api.wheretoeat.review.image-upload');

Route::get('features', FeatureIndexController::class)->name('api.wheretoeat.features');

Route::get('browse', BrowseIndexController::class)->name('api.wheretoeat.browse');

Route::post('browse/search', BrowseSearchStoreController::class)->name('api.wheretoeat.browse.search');

Route::post('check-recommended-place', CheckRecommendedPlaceGetController::class)->name('api.wheretoeat.check-recommended-place');

Route::get('random', RandomShowController::class)->name('api.wheretoeat.random');

Route::get('{eatery}', DetailsShowController::class)->name('api.wheretoeat.get');

Route::post('{eatery}/branches', DetailsBranchesIndexController::class)->name('api.wheretoeat.branches.index');

Route::get('{eatery}/suggest-edit', SuggestEditsIndexController::class)->name('api.wheretoeat.suggest-edit.get');

Route::post('{eatery}/suggest-edit', SuggestEditsStoreController::class)->name('api.wheretoeat.suggest-edit.store');

Route::post('{eatery}/reviews', ReviewStoreController::class)->name('api.wheretoeat.reviews.store');

Route::post('{eatery}/report', ReportStoreController::class)->name('api.wheretoeat.report.store');

Route::get('{eatery}/sealiac', EaterySealiacOverviewGetController::class)->name('api.wheretoeat.sealiac.get');
