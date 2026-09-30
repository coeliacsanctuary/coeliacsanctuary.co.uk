<?php

declare(strict_types=1);

use App\Http\Controllers\Shop\MyTravelCards\IndexController;
use App\Http\Controllers\Shop\MyTravelCards\Login\Confirm\ShowController as LoginConfirmShowController;
use App\Http\Controllers\Shop\MyTravelCards\Login\Confirm\StoreController as LoginConfirmStoreController;
use App\Http\Controllers\Shop\MyTravelCards\Login\ShowController as LoginShowController;
use App\Http\Controllers\Shop\MyTravelCards\Login\StoreController as LoginStoreController;
use App\Http\Controllers\Shop\MyTravelCards\ShowController;
use App\Http\Middleware\MyTravelCards\CustomerOwnsDigitalCardMiddleware;
use App\Http\Middleware\MyTravelCards\HasCustomerEmailInCookieMiddleware;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:customers')->group(function (): void {
    Route::get('/', IndexController::class)->name('my-travel-cards.index');
    Route::get('/card/{card}', ShowController::class)
        ->middleware(CustomerOwnsDigitalCardMiddleware::class)
        ->name('my-travel-cards.show');
});

Route::middleware('guest:customers')->prefix('login')->name('my-travel-cards.login.')->group(function (): void {
    Route::get('', LoginShowController::class)->name('show');
    Route::post('', LoginStoreController::class)
        ->middleware(HandlePrecognitiveRequests::class)
        ->name('store');

    Route::middleware(HasCustomerEmailInCookieMiddleware::class)->group(function (): void {
        Route::get('confirm', LoginConfirmShowController::class)->name('confirm.show');
        Route::post('confirm', LoginConfirmStoreController::class)->name('confirm.store');
    });
});
