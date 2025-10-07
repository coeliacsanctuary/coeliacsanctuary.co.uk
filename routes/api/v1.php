<?php

declare(strict_types=1);

use App\Http\Api\V1\Controllers\EatingOut\GetController as EatingOutGetController;
use App\Http\Api\v1\Controllers\EatingOut\Nearby\IndexController as EatingOutNearbyController;
use Illuminate\Support\Facades\Route;

Route::get('/eating-out/nearby', EatingOutNearbyController::class)->name('eating-out.nearby.index');

Route::get('/eating-out/{eatery}', EatingOutGetController::class)->name('eating-out.get');
Route::get('/eating-out/{eatery}/{nationwideBranch}', EatingOutGetController::class)->name('eating-out.get.branch');
