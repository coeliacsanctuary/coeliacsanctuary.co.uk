<?php

declare(strict_types=1);

use App\Http\Api\V1\Controllers\EatingOut\Eatery\Branches\IndexController as EatingOutListBranchesController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\GetController as EatingOutGetController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\OpeningTimes\GetController as EatingOutOpeningTimesController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Report\StoreController as EatingOutReportStoreController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\Admin\GetController as EatingOutAdminReviewController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\Images\IndexController as EatingOutReviewImagesIndexController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\IndexController as EatingOutReviewIndexController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\SealiacOverview\GetController as EatingOutSealiacOverviewController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\SealiacOverview\Feedback\StoreController as EatingOutSealiacOverviewFeedbackController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\SuggestEdits\GetController as EatingOutSuggestEditsController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\SuggestEdits\StoreController as EatingOutSuggestEditsStoreController;
use App\Http\Api\V1\Controllers\EatingOut\Nearby\IndexController as EatingOutNearbyController;
use Illuminate\Support\Facades\Route;

Route::prefix('/eating-out')->name('eating-out.')->group(function (): void {
    Route::get('/nearby', EatingOutNearbyController::class)->name('nearby');

    Route::prefix('/{eatery}')->name('details.')->group(function (): void {
        Route::get('/opening-times', EatingOutOpeningTimesController::class)->name('opening-times');

        Route::prefix('sealiac-overview')->name('sealiac-overview.')->group(function (): void {
            Route::get('/', EatingOutSealiacOverviewController::class)->name('get');
            Route::post('/', EatingOutSealiacOverviewController::class)->name('feedback');
        });

        Route::prefix('reviews')->name('reviews.')->group(function (): void {
            Route::get('admin-review', EatingOutAdminReviewController::class)->name('admin-review');
            Route::get('images', EatingOutReviewImagesIndexController::class)->name('images.index');
            Route::get('/', EatingOutReviewIndexController::class)->name('index');
        });

        Route::prefix('suggest-edits')->name('suggest-edit.')->group(function (): void {
            Route::get('/', EatingOutSuggestEditsController::class)->name('get');
            Route::post('/', EatingOutSuggestEditsStoreController::class)->name('store');
        });

        Route::get('branches', EatingOutListBranchesController::class)->name('branches');

        Route::post('report', EatingOutReportStoreController::class)->name('report');

        Route::get('/{nationwideBranch}', EatingOutGetController::class)->name('get.branch');
        Route::get('/', EatingOutGetController::class)->name('get');
    });
});
