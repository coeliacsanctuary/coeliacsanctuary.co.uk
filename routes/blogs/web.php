<?php

declare(strict_types=1);

use App\Http\Controllers\Blogs\Feed\IndexController as FeedController;
use App\Http\Controllers\Blogs\IndexController;
use App\Http\Controllers\Blogs\ShowController;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexController::class)->name('blog.index');
Route::get('tags/{tag}', IndexController::class)->name('blog.index.tags');

Route::get('feed', FeedController::class)->name('blog.feed');

Route::get('{blog}', ShowController::class)->name('blog.show');
