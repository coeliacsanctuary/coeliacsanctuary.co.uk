<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Laravel\Nova\Http\Requests\NovaRequest;

Route::get('/', fn (NovaRequest $request) => inertia('EateryCollectionsQueryBuilder'));
