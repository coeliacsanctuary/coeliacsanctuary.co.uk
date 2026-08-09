<?php

declare(strict_types=1);

namespace Tests\Fixtures\MagicRouting;

use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\Response;

class StubResponse implements Responsable
{
    public function toResponse($request): Response
    {
        return response()->noContent();
    }
}
