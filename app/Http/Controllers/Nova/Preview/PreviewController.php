<?php

declare(strict_types=1);

namespace App\Http\Controllers\Nova\Preview;

use App\Http\Response\Inertia;
use App\Models\NovaPreview;
use App\Support\NovaPreview\NovaPreviewResolver;
use Inertia\Response;

class PreviewController
{
    public function __invoke(string $token, Inertia $inertia, NovaPreviewResolver $novaPreviewResolver): Response
    {
        $preview = NovaPreview::query()->where('token', $token)->firstOrFail();

        $renderer = $novaPreviewResolver->handle($preview->model);

        return $inertia
            ->doNotTrack()
            ->render($renderer->component(), $renderer->payload($preview->payload));
    }
}
