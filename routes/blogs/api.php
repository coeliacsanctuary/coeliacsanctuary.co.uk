<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Blogs\GetController as ApiBlogGetController;
use App\Http\Controllers\Api\Blogs\IndexController as ApiBlogIndexController;
use Illuminate\Support\Facades\Route;

Route::get('/', ApiBlogIndexController::class)->name('api.blogs.index');
Route::get('{blog}', ApiBlogGetController::class)->name('api.blogs.show');
