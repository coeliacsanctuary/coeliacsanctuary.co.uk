<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class RequireWebBasicAuthMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/test/basic-auth', fn (): string => 'ok')->middleware('web');
    }

    #[Test]
    #[TestWith(['production'])]
    #[TestWith(['local'])]
    public function itDoesntRequireAuthInAnExcludedEnvironment(string $environment): void
    {
        $this->app['env'] = $environment;

        $this->get('/test/basic-auth')->assertOk();
    }

    #[Test]
    public function itReturnsAnUnauthorisedResponseWithoutCredentials(): void
    {
        $this->app['env'] = 'develop';

        $this->get('/test/basic-auth')
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Basic');
    }

    #[Test]
    public function itReturnsAnUnauthorisedResponseWithTheWrongCredentials(): void
    {
        $this->app['env'] = 'develop';

        $user = $this->create(User::class);

        $this->withBasicAuth($user->email, 'wrong-password')
            ->get('/test/basic-auth')
            ->assertUnauthorized();
    }

    #[Test]
    public function itAllowsAValidUser(): void
    {
        $this->app['env'] = 'develop';

        $user = $this->create(User::class);

        $this->withBasicAuth($user->email, 'password')
            ->get('/test/basic-auth')
            ->assertOk();
    }

    #[Test]
    public function itDoesntLogTheUserIn(): void
    {
        $this->app['env'] = 'develop';

        $user = $this->create(User::class);

        $this->withBasicAuth($user->email, 'password')->get('/test/basic-auth');

        $this->assertGuest();
    }

    #[Test]
    public function itDoesntRequireAuthWhenWebAccessIsEnabled(): void
    {
        $this->app['env'] = 'develop';

        config()->set('coeliac.enable_web_access', true);

        $this->get('/test/basic-auth')->assertOk();
    }

    #[Test]
    public function itStillRequiresAuthWhenOnlyApiAccessIsEnabled(): void
    {
        $this->app['env'] = 'develop';

        config()->set('coeliac.enable_api_access', true);

        $this->get('/test/basic-auth')->assertUnauthorized();
    }
}
