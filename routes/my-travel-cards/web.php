<?php

declare(strict_types=1);

use App\Http\Controllers\Shop\MyTravelCards\Login\ShowController as LoginShowController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:customer')->group(function (): void {
    Route::get('/', fn () => 'hello');
});

Route::get('login', LoginShowController::class)->name('my-travel-cards.login');
