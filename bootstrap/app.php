<?php

declare(strict_types=1);

use App\Console\Commands\ApplyMassDiscountsCommand;
use App\Console\Commands\CheckForMailcoachScheduledEmailsCommand;
use App\Console\Commands\CloseBasketsCommand;
use App\Console\Commands\PrepareShopReviewInvitationsCommand;
use App\Console\Commands\PublishItemsCommand;
use App\Console\Commands\RemoveCollectionsFromHomepageCommand;
use App\Console\Commands\SendAbandonedBasketEmailCommand;
use App\Http\Api\V1\Middleware\ExternalApiSourceMiddleware;
use App\Http\Middleware\AddRouteModelBindingFallbacksMiddleware;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Response\Inertia;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        health: '/up',
        then: function (): void {
            Route::middleware(['api', ExternalApiSourceMiddleware::class])
                ->prefix('api/v1')
                ->name('api.v1.')
                ->group(base_path('routes/api/v1.php'));

            if (config('app.env') === 'local') {
                Route::middleware('web')
                    ->prefix('__dev')
                    ->group(base_path('routes/local.php'));
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(
            prepend: AddRouteModelBindingFallbacksMiddleware::class,
            append: HandleInertiaRequests::class
        );

        $middleware->redirectGuestsTo(fn () => route('nova.pages.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            $errorStatusCodes = [
                Response::HTTP_INTERNAL_SERVER_ERROR,
                Response::HTTP_SERVICE_UNAVAILABLE,
                Response::HTTP_NOT_FOUND,
                Response::HTTP_FORBIDDEN,
            ];

            if ( ! app()->environment(['local', 'testing']) && in_array($response->getStatusCode(), $errorStatusCodes)) {
                $title = match ($response->getStatusCode()) {
                    Response::HTTP_NOT_FOUND, Response::HTTP_FORBIDDEN => 'Page not found',
                    default => 'Something went wrong',
                };

                $description = match ($response->getStatusCode()) {
                    Response::HTTP_NOT_FOUND => 'Sorry, the page you are looking for could not be found.',
                    default => 'Sorry, something has gone wrong and this page has been contaminated with gluten! We\'re doing our best to fix it!',
                };

                return app(Inertia::class)
                    ->title($title)
                    ->metaTags([], false)
                    ->doNotTrack()
                    ->render('Error', [
                        'title' => $title,
                        'description' => $description,
                    ])
                    ->toResponse($request)
                    ->setStatusCode($response->getStatusCode());
            }

            if ($response->getStatusCode() === 419) {
                return back()->with([
                    'message' => 'The page expired, please try again.',
                ]);
            }

            return $response;
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command(CloseBasketsCommand::class)->everyMinute();
        $schedule->command(SendAbandonedBasketEmailCommand::class)->everyMinute();
        $schedule->command(ApplyMassDiscountsCommand::class)->everyMinute();
        $schedule->command(PrepareShopReviewInvitationsCommand::class)->everyMinute();
        $schedule->command(PublishItemsCommand::class)->everyMinute();
        $schedule->command(RemoveCollectionsFromHomepageCommand::class)->everyMinute();
        $schedule->command(CheckForMailcoachScheduledEmailsCommand::class)->everyMinute();

        if (app()->environment('production')) {
            $schedule->command('about')->thenPing('http://beats.envoyer.io/heartbeat/oKkQ7etgPUsSnOW');

            $schedule->command('backup:run')->hourly();
            $schedule->command('backup:clean')->daily()->at('02:00');
        }
    })
    ->create();
