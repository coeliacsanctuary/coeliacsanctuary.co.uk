<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Spatie\LaravelMobilePass\Models\MobilePass;
use Symfony\Component\HttpFoundation\Response;

class StubMobilePass extends MobilePass
{
    public function toResponse($request): Response
    {
        return new Response('stub-pkpass', Response::HTTP_OK, [
            'Content-Type' => 'application/vnd.apple.pkpass',
        ]);
    }
}
