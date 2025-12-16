<?php

declare(strict_types=1);

use App\Actions\GetPopupCtaAction;
use App\Http\Controllers\Api\MailcoachSchedule\StoreController as MailcoachScheduleStoreController;
use App\Http\Controllers\Api\SealiacOverviewFeedback\StoreController as SealiacOverviewFeedbackController;
use App\Http\Middleware\MailcoachIncomingRequestMiddleware;
use App\Models\TempMailcoachMail;
use Illuminate\Support\Facades\Route;

Route::prefix('blogs')->group(base_path('routes/blogs/api.php'));
Route::prefix('recipes')->group(base_path('routes/recipes/api.php'));
Route::prefix('shop')->group(base_path('routes/shop/api.php'));
Route::prefix('wheretoeat')->group(base_path('routes/eating-out/api.php'));

Route::get('app-request-token', fn () => ['token' => csrf_token()])->middleware('web');

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

Route::get('mailcoach-message/{message}', fn (TempMailcoachMail $message) => $message->message)
    ->name('api.mailcoach-message')
    ->middleware(MailcoachIncomingRequestMiddleware::class);

Route::post('sealiac-overview-feedback/{sealiacOverview}', SealiacOverviewFeedbackController::class)->name('api.sealiac-overview-feedback');
