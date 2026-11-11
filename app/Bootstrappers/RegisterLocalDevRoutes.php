<?php

declare(strict_types=1);

namespace App\Bootstrappers;

use Illuminate\Support\Facades\Route;

class RegisterLocalDevRoutes
{
    public static function handle(): void
    {
        Route::middleware('web')
            ->prefix('__dev')
            ->group(base_path('routes/local.php'));
    }
}
