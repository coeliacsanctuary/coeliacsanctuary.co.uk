<?php

declare(strict_types=1);

namespace Tests\Unit\Bootstrappers;

use App\Actions\Bootstrap\RespondToExceptionAction;
use App\Bootstrappers\RegisterExceptionHandler;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegisterExceptionHandlerTest extends TestCase
{
    protected Handler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new Handler($this->app);
    }

    #[Test]
    public function itRedirectsAnInvalidSignatureOnTheMyTravelCardsOrderLoginRouteToTheLoginPage(): void
    {
        RegisterExceptionHandler::handle(new Exceptions($this->handler));

        $response = $this->handler->render($this->requestForRoute('my-travel-cards.login.order.get'), new InvalidSignatureException());

        $this->assertTrue($response->isRedirect(route('my-travel-cards.login.show')));
        $this->assertEquals('Your link has expired, please enter your email to access your travel cards', session('errors')->first('email'));
    }

    #[Test]
    public function itDoesntRedirectAnInvalidSignatureOnAnyOtherRoute(): void
    {
        RegisterExceptionHandler::handle(new Exceptions($this->handler));

        $response = $this->handler->render($this->requestForRoute('home'), new InvalidSignatureException());

        $this->assertEquals(403, $response->getStatusCode());
    }

    #[Test]
    public function itUsesTheRespondToExceptionActionToFinaliseResponses(): void
    {
        $this->expectAction(RespondToExceptionAction::class, return: new Response('from the action'));

        RegisterExceptionHandler::handle(new Exceptions($this->handler));

        $response = $this->handler->render($this->requestForRoute('home'), new InvalidSignatureException());

        $this->assertEquals('from the action', $response->getContent());
    }

    protected function requestForRoute(string $name): Request
    {
        $route = $this->app['router']->getRoutes()->getByName($name);

        $request = Request::create('/foo');
        $request->setRouteResolver(fn () => $route);

        return $request;
    }
}
