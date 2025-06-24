<?php

declare(strict_types=1);

use App\Http\Controllers\EatingOut\Browse\ShowController as BrowseShowController;
use App\Http\Controllers\EatingOut\CoeliacSanctuaryOnTheGo\ShowController as AppShowController;
use App\Http\Controllers\EatingOut\County\ShowController as CountyShowController;
use App\Http\Controllers\EatingOut\County\Town\ShowController as TownShowController;
use App\Http\Controllers\EatingOut\EateryDetails\GetController as EateryDetailsGetController;
use App\Http\Controllers\EatingOut\IndexController;
use App\Http\Controllers\EatingOut\LandingPage\IndexController as LandingPageIndexController;
use App\Http\Controllers\EatingOut\London\Borough\ShowController as LondonBoroughShowController;
use App\Http\Controllers\EatingOut\London\Borough\Area\ShowController as LondonBoroughAreaShowController;
use App\Http\Controllers\EatingOut\London\IndexController as LondonIndexController;
use App\Http\Controllers\EatingOut\Nationwide\IndexController as NationwideIndexController;
use App\Http\Controllers\EatingOut\RecommendAPlace\CreateController as RecommendAPlaceCreateController;
use App\Http\Controllers\EatingOut\RecommendAPlace\StoreController as RecommendAPlaceStoreController;
use App\Http\Controllers\EatingOut\ReportEatery\StoreController as ReportEateryStoreController;
use App\Http\Controllers\EatingOut\Review\StoreController as ReviewStoreController;
use App\Http\Controllers\EatingOut\Search\ShowController as SearchShowController;
use App\Http\Controllers\EatingOut\Search\StoreController as SearchStoreController;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::get('/eating-out', LandingPageIndexController::class)->name('eating-out.landing');

Route::prefix('wheretoeat')->group(function (): void {
    $prefixedEateryRoutes = function (string $namePrefix): callable {
        return function () use ($namePrefix): void {
            Route::get('/', EateryDetailsGetController::class)->name($namePrefix);

            Route::post('/reviews', ReviewStoreController::class)
                ->middleware([HandlePrecognitiveRequests::class])
                ->name("{$namePrefix}.reviews.create");

            Route::post('/report', ReportEateryStoreController::class)
                ->middleware([HandlePrecognitiveRequests::class])
                ->name("{$namePrefix}.report.create");
        };
    };

    Route::get('/', IndexController::class)->name('eating-out.index');

    Route::post('/search', SearchStoreController::class)->name('eating-out.search.create');
    Route::get('/search/{eaterySearchTerm}', SearchShowController::class)->name('eating-out.search.show');

    Route::get('/nationwide', NationwideIndexController::class)->name('eating-out.nationwide');
    Route::prefix('/nationwide/{eatery}')->group(
        $prefixedEateryRoutes('eating-out.nationwide.show')
    );
    Route::prefix('/nationwide/{eatery}/{nationwideBranch}')->group(
        $prefixedEateryRoutes('eating-out.nationwide.show.branch')
    );

    Route::get('/london', LondonIndexController::class)->name('eating-out.london');
    Route::get('/london/{borough}', LondonBoroughShowController::class)->name('eating-out.london.borough');
    Route::get('/london/{borough}/{area}', LondonBoroughAreaShowController::class)->name('eating-out.london.borough.area');
    Route::prefix('/london/{town}/{area}/{eatery}')->group($prefixedEateryRoutes('eating-out.london.borough.area.show'));

    Route::get('/recommend-a-place', RecommendAPlaceCreateController::class)->name('eating-out.recommend.index');
    Route::post('/recommend-a-place', RecommendAPlaceStoreController::class)->name('eating-out.recommend.create');

    Route::get('/browse', BrowseShowController::class)->name('eating-out.browse');
    Route::get('/browse/{any}', BrowseShowController::class)->where('any', '.*')->name('eating-out.browse.any');

    Route::get('coeliac-sanctuary-on-the-go', AppShowController::class)->name('eating-out.app');

    Route::get('/{county}', CountyShowController::class)->name('eating-out.county');
    Route::get('/{county}/{town}', TownShowController::class)->name('eating-out.town');

    Route::prefix('/{county}/{town}/{eatery}')->group($prefixedEateryRoutes('eating-out.show'));
});
