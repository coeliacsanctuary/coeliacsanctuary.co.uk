<?php

declare(strict_types=1);

namespace App\Actions\Bootstrap;

use App\Http\Response\Inertia;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RespondToExceptionAction
{
    public function handle(Response $response, Throwable $exception, Request $request): Response
    {
        $errorStatusCodes = [
            Response::HTTP_INTERNAL_SERVER_ERROR,
            Response::HTTP_SERVICE_UNAVAILABLE,
            Response::HTTP_NOT_FOUND,
            Response::HTTP_FORBIDDEN,
        ];

        if ( ! app()->environment(['local', 'testing']) && in_array($response->getStatusCode(), $errorStatusCodes, true)) {
            $title = match ($response->getStatusCode()) {
                Response::HTTP_NOT_FOUND, Response::HTTP_FORBIDDEN => 'Page not found',
                default => 'Something went wrong',
            };

            $description = match ($response->getStatusCode()) {
                Response::HTTP_NOT_FOUND => 'Sorry, the page you are looking for could not be found.',
                default => "Sorry, something has gone wrong and this page has been contaminated with gluten! We're doing our best to fix it!",
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
    }
}
