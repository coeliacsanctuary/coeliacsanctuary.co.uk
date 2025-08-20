<?php

declare(strict_types=1);

use App\Http\Api\v1\Controllers\EatingOut\Nearby\IndexController as EatingOutNearbyController;
use Illuminate\Support\Facades\Route;

Route::get('/eating-out/nearby', EatingOutNearbyController::class)->name('eating-out.nearby');
