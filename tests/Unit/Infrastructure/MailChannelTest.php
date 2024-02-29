<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use App\Infrastructure\MailChannel;
use App\Models\NotificationEmail;
use App\Models\Shop\ShopCustomer;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use Tests\Fixtures\MockMjmlNotification;
use Tests\Fixtures\MockNotification;
use Tests\TestCase;

class MailChannelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    /** @test */
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

    /** @test */
    public function itStoresTheEmailInTheDatabase(): void
    {
        $this->assertDatabaseEmpty(NotificationEmail::class);

        app(MailChannel::class)->send(
            $this->create(ShopCustomer::class),
            new MockMjmlNotification(),
        );

        $this->assertDatabaseCount(NotificationEmail::class, 1);
    }

    /** @test */
    public function itCompilesMjml(): void
    {
        $mock = $this->partialMock(MailChannel::class);

        invade($mock)->__set('mailer', app(Mailer::class));

        $mock->shouldAllowMockingProtectedMethods()
            ->shouldReceive('buildMjml')
            ->once();

        app(MailChannel::class)->send(
            $this->create(ShopCustomer::class),
            new MockMjmlNotification(),
        );
    }
}
