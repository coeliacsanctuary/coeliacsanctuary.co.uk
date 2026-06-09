<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Email;

use App\Models\NotificationEmail;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Mjml\Mjml;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    #[Test]
    public function itReturnsNotFoundIfAnEmailDoesntExist(): void
    {
        $this->get(route('email.show', ['email' => 'foo']))->assertNotFound();
    }

    #[Test]
    public function itUsesTheMjmlService(): void
    {
        $email = $this->create(NotificationEmail::class);

        $this->mock(Mjml::class)
            ->shouldReceive('sidecar')
            ->andReturnSelf()
            ->once()
            ->getMock()
            ->shouldReceive('minify')
            ->andReturnSelf()
            ->once()
            ->getMock()
            ->shouldReceive('toHtml')
            ->once()
            ->andReturn('foo');

        $this->get(route('email.show', $email))
            ->assertOk()
            ->assertContent('foo');
    }
}
