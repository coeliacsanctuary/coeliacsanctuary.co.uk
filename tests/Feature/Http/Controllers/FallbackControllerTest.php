<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Actions\CheckForRouteRedirectAction;
use App\Models\Redirect;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FallbackControllerTest extends TestCase
{
    #[Test]
    public function itCallsTheCheckForRouteRedirectAction(): void
    {
        $this->mock(CheckForRouteRedirectAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($argPath) {
                $this->assertEquals('foobar', $argPath);

                return true;
            })
            ->once();

        $this->get('foobar');
    }

    #[Test]
    public function ifCheckForRouteRedirectActionReturnsARedirectInstanceItRedirectsUsingTheGivenToAndStatus(): void
    {
        $redirect = $this->create(Redirect::class, [
            'to' => '/blog',
            'status' => Response::HTTP_TEMPORARY_REDIRECT,
        ]);

        $this->mock(CheckForRouteRedirectAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($argPath) {
                $this->assertEquals('foobar', $argPath);

                return true;
            })
            ->andReturn($redirect)
            ->once();

        $this->get('foobar')
            ->assertStatus($redirect->status)
            ->assertRedirect($redirect->to);
    }

    #[Test]
    public function ifCheckForRouteRedirectActionReturnsARedirectInstanceItIncrementsTheHitsCount(): void
    {
        $redirect = $this->create(Redirect::class, [
            'to' => '/blog',
            'status' => Response::HTTP_TEMPORARY_REDIRECT,
            'hits' => 5,
        ]);

        $this->mock(CheckForRouteRedirectAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($argPath) {
                $this->assertEquals('foobar', $argPath);

                return true;
            })
            ->andReturn($redirect)
            ->once();

        $this->get('foobar');

        $this->assertEquals(6, $redirect->refresh()->hits);
    }

    #[Test]
    public function ifCheckForRouteRedirectActionReturnsNullItReturnsNotFound(): void
    {
        $this->mock(CheckForRouteRedirectAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($argPath) {
                $this->assertEquals('foobar', $argPath);

                return true;
            })
            ->andReturnNull()
            ->once();

        $this->get('foobar')->assertNotFound();
    }
}
