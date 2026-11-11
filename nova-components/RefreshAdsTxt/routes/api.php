<?php

declare(strict_types=1);

use App\Actions\FetchAdsTxtAction;
use Illuminate\Support\Facades\Route;

Route::post('/', function (FetchAdsTxtAction $fetchAdsTxtAction) {
    $fetchAdsTxtAction->handle();

    return response()->noContent();
});
