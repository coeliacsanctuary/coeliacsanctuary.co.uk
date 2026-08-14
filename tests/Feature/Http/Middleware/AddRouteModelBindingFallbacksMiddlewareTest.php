<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Actions\Redirects\CheckForRouteRedirectAction;
use App\Actions\Redirects\HandleRedirectResponseAction;
use App\Models\Blogs\Blog;
use App\Models\Redirect;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddRouteModelBindingFallbacksMiddlewareTest extends TestCase
{
    #[Test]
    public function itDoesntApplyTheMissingActionToARouteThatAlreadyHasMissing(): void
    {
        $controllerHit = false;
        $missingHit = false;

        Route::get('/test/blog/{blog}', function (Blog $blog) use (&$controllerHit): void {
            $controllerHit = true;
        })->middleware('web')->missing(function ($request, $exception) use (&$missingHit): void {
            $missingHit = true;

            throw $exception;
        });

        $blog = $this->create(Blog::class, [
            'live' => false,
        ]);

        $this->mock(CheckForRouteRedirectAction::class)->shouldNotReceive('handle');

        $this->get('/test/blog/' . $blog->slug)->assertNotFound();

        $this->assertFalse($controllerHit);
        $this->assertTrue($missingHit);
    }

    #[Test]
    public function itCallsTheCheckForRouteRedirectActionWhenARouteModelBindingCantBeFound(): void
    {
        $blog = $this->create(Blog::class, [
            'live' => false,
        ]);

        $this->mock(CheckForRouteRedirectAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($argPath) use ($blog) {
                $this->assertEquals("blog/{$blog->slug}", $argPath);

                return true;
            })
            ->andReturnNull()
            ->once();

        $this->get(route('blog.show', $blog));
    }

    #[Test]
    public function ifCheckForRouteRedirectActionReturnsARedirectInstanceItCalsTheHandleRedirectResponseAction(): void
    {
        $blog = $this->create(Blog::class, [
            'live' => false,
        ]);

        $redirect = $this->create(Redirect::class, [
            'to' => '/blog',
            'status' => Response::HTTP_TEMPORARY_REDIRECT,
            'hits' => 5,
        ]);

        $this->mock(CheckForRouteRedirectAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($argPath) use ($blog) {
                $this->assertEquals("blog/{$blog->slug}", $argPath);

                return true;
            })
            ->andReturn($redirect)
            ->once();

        $this->mock(HandleRedirectResponseAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($redirectArg) use ($redirect) {
                $this->assertTrue($redirect->is($redirectArg));

                return true;
            })
            ->once();

        $this->get(route('blog.show', $blog));
    }

    #[Test]
    public function ifCheckForRouteRedirectActionReturnsNullItThrowsTheBubbledUpException(): void
    {
        $blog = $this->create(Blog::class, [
            'live' => false,
        ]);

        $this->mock(CheckForRouteRedirectAction::class)
            ->shouldReceive('handle')
            ->withArgs(function ($argPath) use ($blog) {
                $this->assertEquals("blog/{$blog->slug}", $argPath);

                return true;
            })
            ->andReturnNull()
            ->once();

        $this->withoutExceptionHandling();
        $this->expectException(ModelNotFoundException::class);

        $this->get(route('blog.show', $blog));
    }
}
