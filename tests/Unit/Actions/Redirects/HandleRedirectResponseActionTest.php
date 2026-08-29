<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Redirects;

use App\Actions\Redirects\HandleRedirectResponseAction;
use App\Models\Redirect;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HandleRedirectResponseActionTest extends TestCase
{
    #[Test]
    public function itIncrementsTheHitsCount(): void
    {
        $redirect = $this->create(Redirect::class, [
            'to' => '/blog',
            'status' => Response::HTTP_TEMPORARY_REDIRECT,
            'hits' => 5,
        ]);

        app(HandleRedirectResponseAction::class)->handle($redirect);

        $this->assertEquals(6, $redirect->refresh()->hits);
    }

    #[Test]
    public function itReturnsARedirectToTheCorrectUrlWithTheCorrectStatus(): void
    {
        $redirect = $this->create(Redirect::class, [
            'to' => '/blog',
            'status' => Response::HTTP_TEMPORARY_REDIRECT,
        ]);

        $response = app(HandleRedirectResponseAction::class)->handle($redirect);

        $this->assertStringEndsWith('/blog', $response->getTargetUrl());
        $this->assertEquals(Response::HTTP_TEMPORARY_REDIRECT, $response->status());
    }
}
