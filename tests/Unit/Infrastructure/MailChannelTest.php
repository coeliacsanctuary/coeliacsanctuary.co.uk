<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\MailChannel;
use App\Models\NotificationEmail;
use App\Models\Shop\ShopCustomer;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use Spatie\Mjml\Mjml;
use Tests\Fixtures\MockMjmlNotification;
use Tests\Fixtures\MockNotification;
use Tests\TestCase;

class MailChannelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Process::fake();
    }

    #[Test]
    public function itBypassesTheCustomImplementationIfItIsNotAnInstanceOfAnMjmlMessage(): void
    {
        $this->partialMock(MailChannel::class)
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('send')
            ->getMock()
            ->shouldNotReceive('buildMjml');

        app(MailChannel::class)->send(
            $this->create(ShopCustomer::class),
            new MockNotification(),
        );
    }

    #[Test]
    public function itStoresTheEmailInTheDatabase(): void
    {
        $this->assertDatabaseEmpty(NotificationEmail::class);

        $this->mock(Mjml::class)
            ->shouldReceive('minify')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('toHtml')
            ->andReturn('<html></html>');

        app(MailChannel::class)->send(
            $this->create(ShopCustomer::class),
            new MockMjmlNotification(),
        );

        $this->assertDatabaseCount(NotificationEmail::class, 1);
    }

    #[Test]
    public function itCompilesMjml(): void
    {
        $mock = $this->partialMock(MailChannel::class);

        invade($mock)->__set('mailer', app(Mailer::class));

        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('buildMjml')
            ->once();

        $this->mock(Mjml::class)
            ->shouldReceive('minify')
            ->andReturnSelf()
            ->getMock()
            ->shouldReceive('toHtml')
            ->andReturn('<html></html>');

        app(MailChannel::class)->send(
            $this->create(ShopCustomer::class),
            new MockMjmlNotification(),
        );
    }
}
