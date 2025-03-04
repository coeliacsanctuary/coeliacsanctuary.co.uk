<?php

declare(strict_types=1);

use App\Actions\GetPopupCtaAction;
use App\Http\Controllers\Api\MailcoachSchedule\StoreController as MailcoachScheduleStoreController;
use App\Http\Middleware\MailcoachIncomingRequestMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('blogs')->group(base_path('routes/blogs/api.php'));
Route::prefix('recipes')->group(base_path('routes/recipes/api.php'));
Route::prefix('shop')->group(base_path('routes/shop/api.php'));
Route::prefix('wheretoeat')->group(base_path('routes/eating-out/api.php'));

Route::get('app-request-token', fn () => ['token' => csrf_token()]);

Route::get('popup', function (GetPopupCtaAction $getPopupCtaAction) {
    $popup = $getPopupCtaAction->handle();

    if ( ! $popup) {
        return [];
    }

    return [
        'id' => $popup->id,
        'text' => $popup->text,
        'link' => $popup->link,
        'image' => $popup->main_image,
    ];
});

Route::post('mailcoach-schedule', MailcoachScheduleStoreController::class)
    ->name('api.mailcoach-schedule')
    ->middleware(MailcoachIncomingRequestMiddleware::class);
