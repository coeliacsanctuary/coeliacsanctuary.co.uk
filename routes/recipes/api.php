<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Recipes\IndexController as ApiRecipeIndexController;
use Illuminate\Support\Facades\Route;

Route::get('/', ApiRecipeIndexController::class)->name('api.recipes.index');
