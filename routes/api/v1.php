<?php

declare(strict_types=1);

use App\Http\Api\V1\Controllers\EatingOut\Eatery\GetController as EatingOutGetController;
use App\Http\Api\V1\Controllers\EatingOut\Nearby\IndexController as EatingOutNearbyController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\Admin\GetController as EatingOutAdminReviewController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\Images\IndexController as EatingOutReviewImagesIndexController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\IndexController as EatingOutReviewIndexController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\SealiacOverview\GetController as EatingOutSealiacOverviewController;
use App\Http\Api\V1\Controllers\EatingOut\Eatery\OpeningTimes\GetController as EatingOutOpeningTimesController;
use Illuminate\Support\Facades\Route;

Route::prefix('/eating-out')->name('eating-out.')->group(function (): void {
    Route::get('/nearby', EatingOutNearbyController::class)->name('nearby');

    Route::prefix('/{eatery}')->name('details.')->group(function (): void {
        Route::get('/opening-times', EatingOutOpeningTimesController::class)->name('opening-times');
        Route::get('/sealiac-overview', EatingOutSealiacOverviewController::class)->name('sealiac-overview');

        Route::prefix('reviews')->name('reviews.')->group(function (): void {
            Route::get('admin-review', EatingOutAdminReviewController::class)->name('admin-review');
            Route::get('images', EatingOutReviewImagesIndexController::class)->name('images.index');
            Route::get('/', EatingOutReviewIndexController::class)->name('index');
        });

        Route::get('/{nationwideBranch}', EatingOutGetController::class)->name('get.branch');
        Route::get('/', EatingOutGetController::class)->name('get');
    });
});
