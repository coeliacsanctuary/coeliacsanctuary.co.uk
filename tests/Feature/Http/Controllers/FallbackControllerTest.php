<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Contracts\RouteFallbackResolverContract;
use App\Support\RouteFallbackResolvers\RedirectFallbackResolver;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FallbackControllerTest extends TestCase
{
    /** @param class-string<RouteFallbackResolverContract>[] $handlers  */
    protected function mockResolversToReject(array $handlers): void
    {
        foreach ($handlers as $handler) {
            $this->mock($handler)
                ->shouldReceive('canHandle')
                ->andReturnFalse()
                ->once()
                ->getMock()
                ->shouldNotReceive('handle');
        }
    }

    #[Test]
    public function itChecksTheRedirectFallbackResolverIfNoOtherResolvesReturnHandleable(): void
    {
        /** @var class-string<RouteFallbackResolverContract>[] $handlersToReject */
        $handlersToReject = [];

        $this->mockResolversToReject($handlersToReject);

        $this->mock(RedirectFallbackResolver::class)
            ->shouldReceive('canHandle')
            ->andReturnTrue()
            ->once()
            ->getMock()
            ->shouldReceive('handle')
            ->once();

        $this->get('foobar');
    }

    #[Test]
    public function ifAllResolversReturnNotHandleableItReturnsNotFound(): void
    {
        /** @var class-string<RouteFallbackResolverContract>[] $handlersToReject */
        $handlersToReject = [
            RedirectFallbackResolver::class,
        ];

        $this->mockResolversToReject($handlersToReject);

        $this->get('foobar')->assertNotFound();
    }
}
