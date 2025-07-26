<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CheckForRouteRedirectAction;
use App\Models\Redirect;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckForRouteRedirectActionTest extends TestCase
{
    #[Test]
    public function itWillReturnARedirectInstanceIfTheRequestedUrlExistsInTheRedirectsTable(): void
    {
        $redirect = $this->create(Redirect::class, [
            'from' => '/foobar',
            'to' => '/blog',
        ]);

        $this->assertTrue($redirect->is(app(CheckForRouteRedirectAction::class)->handle('/foobar')));
    }

    #[Test]
    public function itCanHandleWildcardsInTheFrom(): void
    {
        $redirect = $this->create(Redirect::class, [
            'from' => '/foo-*',
            'to' => '/blog',
        ]);

        $this->assertTrue($redirect->is(app(CheckForRouteRedirectAction::class)->handle('/foo-bar')));
    }

    #[Test]
    public function itCanHandleRegex(): void
    {
        $redirect = $this->create(Redirect::class, [
            'from' => '/foo-bar$',
            'to' => '/blog',
        ]);

        $this->assertNull(app(CheckForRouteRedirectAction::class)->handle('/foo-bar-baz'));

        $this->assertTrue($redirect->is(app(CheckForRouteRedirectAction::class)->handle('/foo-bar')));

        $secondRedirect = $this->create(Redirect::class, [
            'from' => '/baz-[0-9]',
            'to' => '/blog',
        ]);

        $this->assertTrue($secondRedirect->is(app(CheckForRouteRedirectAction::class)->handle('/baz-5')));
    }

    #[Test]
    public function itReturnsNullIfNoMatchingRecordExists(): void
    {
        $this->assertNull(app(CheckForRouteRedirectAction::class)->handle('/foo'));
    }
}
