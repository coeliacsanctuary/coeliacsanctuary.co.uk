<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Bootstrap;

use App\Actions\Bootstrap\RespondToExceptionAction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class RespondToExceptionActionTest extends TestCase
{
    #[Test]
    #[TestWith(['local'])]
    #[TestWith(['testing'])]
    public function itReturnsTheResponseUnchangedInLocalAndTesting(string $environment): void
    {
        $this->app['env'] = $environment;

        $response = new Response('original', 404);

        $this->assertSame($response, $this->callAction(RespondToExceptionAction::class, $response, new Exception(), Request::create('/foo')));
    }

    #[Test]
    #[TestWith([404])]
    #[TestWith([403])]
    public function itRendersThePageNotFoundErrorPage(int $statusCode): void
    {
        $this->app['env'] = 'production';

        $response = $this->callAction(RespondToExceptionAction::class, new Response('', $statusCode), new Exception(), Request::create('/foo'));

        TestResponse::fromBaseResponse($response)
            ->assertStatus($statusCode)
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Error')
                ->where('title', 'Page not found')
                ->where('description', $statusCode === 404 ? 'Sorry, the page you are looking for could not be found.' : "Sorry, something has gone wrong and this page has been contaminated with gluten! We're doing our best to fix it!"));
    }

    #[Test]
    #[TestWith([500])]
    #[TestWith([503])]
    public function itRendersTheSomethingWentWrongErrorPage(int $statusCode): void
    {
        $this->app['env'] = 'production';

        $response = $this->callAction(RespondToExceptionAction::class, new Response('', $statusCode), new Exception(), Request::create('/foo'));

        TestResponse::fromBaseResponse($response)
            ->assertStatus($statusCode)
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('Error')
                ->where('title', 'Something went wrong')
                ->where('description', "Sorry, something has gone wrong and this page has been contaminated with gluten! We're doing our best to fix it!"));
    }

    #[Test]
    public function itRedirectsBackWithAMessageWhenThePageHasExpired(): void
    {
        $response = $this->callAction(RespondToExceptionAction::class, new Response('', 419), new Exception(), Request::create('/foo'));

        $this->assertTrue($response->isRedirect());
        $this->assertEquals('The page expired, please try again.', session('message'));
    }

    #[Test]
    public function itReturnsTheResponseUnchangedForOtherStatusCodes(): void
    {
        $this->app['env'] = 'production';

        $response = new Response('original', 401);

        $this->assertSame($response, $this->callAction(RespondToExceptionAction::class, $response, new Exception(), Request::create('/foo')));
    }
}
