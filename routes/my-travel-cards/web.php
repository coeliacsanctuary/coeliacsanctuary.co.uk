<?php

declare(strict_types=1);

use App\Http\Controllers\Shop\MyTravelCards\Login\Confirm\ShowController as LoginConfirmShowController;
use App\Http\Controllers\Shop\MyTravelCards\Login\ShowController as LoginShowController;
use App\Http\Controllers\Shop\MyTravelCards\Login\StoreController as LoginStoreController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:customer')->group(function (): void {
    Route::get('/', fn () => 'hello');
});

Route::middleware('guest:customer')->prefix('login')->name('my-travel-cards.login.')->group(function (): void {
    Route::get('', LoginShowController::class)->name('show');
    Route::post('', LoginStoreController::class)->name('store');

    Route::get('confirm', LoginConfirmShowController::class)->name('confirm.show');
});
