<?php

declare(strict_types=1);

use App\Http\Api\V1\Controllers\Blogs\IndexController as BlogIndexController;
use App\Http\Api\V1\Controllers\EatingOut\Browse\IndexController as EatingOutBrowseController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Branches\IndexController as EatingOutListBranchIndexController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Branches\Summary\IndexController as EatingOutListBranchSummaryController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\GetController as EatingOutGetController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\OpeningTimes\GetController as EatingOutOpeningTimesController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Report\StoreController as EatingOutReportStoreController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\Admin\GetController as EatingOutAdminReviewController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\Images\IndexController as EatingOutReviewImagesIndexController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\Images\StoreController as EatingOutReviewImagesStoreController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\IndexController as EatingOutReviewIndexController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\StoreController as EatingOutReviewStoreController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\SealiacOverview\Feedback\StoreController as EatingOutSealiacOverviewFeedbackController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\SealiacOverview\GetController as EatingOutSealiacOverviewController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\SuggestEdits\GetController as EatingOutSuggestEditsController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\SuggestEdits\StoreController as EatingOutSuggestEditsStoreController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Summary\GetController as EatingOutSummaryController;
use App\Http\Api\V1\Controllers\EatingOut\Explore\IndexController as EatingOutExploreController;
use App\Http\Api\V1\Controllers\EatingOut\Filters\GetController as EatingOutFiltersController;
use App\Http\Api\V1\Controllers\EatingOut\Geocode\GetController as EatingOutGeocodeController;
use App\Http\Api\V1\Controllers\EatingOut\Nationwide\IndexController as EatingOutNationwideIndexController;
use App\Http\Api\V1\Controllers\EatingOut\Nearby\IndexController as EatingOutNearbyController;
use App\Http\Api\V1\Controllers\EatingOut\RecommendAPlace\Check\StoreController as EatingOutRecommendAPlaceCheckStoreController;
use App\Http\Api\V1\Controllers\EatingOut\RecommendAPlace\StoreController as EatingOutRecommendAPlaceStoreController;
use App\Http\Api\V1\Controllers\EatingOut\VenueTypes\IndexController as EatingOutVenueTypesIndexController;
use App\Http\Api\V1\Controllers\Recipes\IndexController as RecipeIndexController;
use App\Http\Api\V1\Controllers\ShopCta\GetController as ShopCtaGetController;
use App\Http\Api\V1\Controllers\WebsiteImage\GetController as WebsiteImageGetController;
use Illuminate\Support\Facades\Route;

Route::get('/blogs', BlogIndexController::class)->name('blogs.index');
Route::get('/recipes', RecipeIndexController::class)->name('recipes.index');

Route::get('shop-cta', ShopCtaGetController::class)->name('shop-cta.get');
Route::get('website-img', WebsiteImageGetController::class)->name('website-img.get');

Route::prefix('/eating-out')->name('eating-out.')->group(function (): void {
    Route::get('/browse', EatingOutBrowseController::class)->name('browse');
    Route::get('/explore', EatingOutExploreController::class)->name('explore');
    Route::get('/nearby', EatingOutNearbyController::class)->name('nearby');

    Route::get('/filters', EatingOutFiltersController::class)->name('filters');
    Route::post('/geocode', EatingOutGeocodeController::class)->name('geocode');

    Route::get('/venue-types', EatingOutVenueTypesIndexController::class)->name('venue-types');

    Route::post('recommend-a-place/', EatingOutRecommendAPlaceStoreController::class)->name('recommend-a-place.store');
    Route::post('recommend-a-place/check', EatingOutRecommendAPlaceCheckStoreController::class)->name('recommend-a-place.check.store');

    Route::get('/nationwide', EatingOutNationwideIndexController::class)->name('nationwide.index');

    Route::prefix('/{eatery}')->name('details.')->group(function (): void {
        Route::get('/opening-times', EatingOutOpeningTimesController::class)->name('opening-times');

        Route::prefix('sealiac-overview')->name('sealiac-overview.')->group(function (): void {
            Route::get('/', EatingOutSealiacOverviewController::class)->name('get');
            Route::post('/', EatingOutSealiacOverviewFeedbackController::class)->name('feedback');
        });

        Route::prefix('reviews')->name('reviews.')->group(function (): void {
            Route::get('admin-review', EatingOutAdminReviewController::class)->name('admin-review');
            Route::get('images', EatingOutReviewImagesIndexController::class)->name('images.index');
            Route::post('images', EatingOutReviewImagesStoreController::class)->name('images.store');
            Route::get('/', EatingOutReviewIndexController::class)->name('index');
            Route::post('/', EatingOutReviewStoreController::class)->name('store');
        });

        Route::prefix('suggest-edits')->name('suggest-edit.')->group(function (): void {
            Route::get('/', EatingOutSuggestEditsController::class)->name('get');
            Route::post('/', EatingOutSuggestEditsStoreController::class)->name('store');
        });

        Route::get('branches', EatingOutListBranchIndexController::class)->name('branches.index');
        Route::get('branches/summary', EatingOutListBranchSummaryController::class)->name('branches.summary.index');

        Route::post('report', EatingOutReportStoreController::class)->name('report');

        Route::get('/summary', EatingOutSummaryController::class)->name('summary');

        Route::get('/{nationwideBranch}', EatingOutGetController::class)->name('get.branch');
        Route::get('/', EatingOutGetController::class)->name('get');
    });
});
