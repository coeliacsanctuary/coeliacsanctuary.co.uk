<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners;

use App\Listeners\RestrictNonProductionMailListener;
use Illuminate\Mail\Events\MessageSending;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class RestrictNonProductionMailListenerTest extends TestCase
{
    protected function makeEvent(string $to): MessageSending
    {
        return new MessageSending(new Email()->to($to)->subject('Foo Bar Baz'));
    }

    protected function useHerdMailer(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
        ]);
    }

    #[Test]
    public function itSendsEverythingAsIsInProduction(): void
    {
        $this->app['env'] = 'production';

        $event = $this->makeEvent('someone@example.com');

        $this->assertNull(app(RestrictNonProductionMailListener::class)->handle($event));
        $this->assertEquals('Foo Bar Baz', $event->message->getSubject());
    }

    #[Test]
    public function itSendsEverythingAsIsLocallyWhenUsingSmtpOnLocalhost(): void
    {
        $this->app['env'] = 'local';
        $this->useHerdMailer();

        $event = $this->makeEvent('someone@example.com');

        $this->assertNull(app(RestrictNonProductionMailListener::class)->handle($event));
        $this->assertEquals('Foo Bar Baz', $event->message->getSubject());
    }

    #[Test]
    public function itPrefixesTheSubjectLocallyWhenNotUsingSmtpOnLocalhostAndTheToIsWhitelisted(): void
    {
        $this->app['env'] = 'local';

        $event = $this->makeEvent('contact@coeliacsanctuary.co.uk');

        $this->assertNull(app(RestrictNonProductionMailListener::class)->handle($event));
        $this->assertEquals('[local] Foo Bar Baz', $event->message->getSubject());
    }

    #[Test]
    public function itBlocksTheEmailLocallyWhenNotUsingSmtpOnLocalhostAndTheToIsNotWhitelisted(): void
    {
        $this->app['env'] = 'local';

        $event = $this->makeEvent('someone@example.com');

        $this->assertFalse(app(RestrictNonProductionMailListener::class)->handle($event));
    }

    #[Test]
    public function itPrefixesTheSubjectWithTheEnvironmentWhenTheToIsWhitelisted(): void
    {
        $this->app['env'] = 'develop';
        $this->useHerdMailer();

        $event = $this->makeEvent('contact@coeliacsanctuary.co.uk');

        $this->assertNull(app(RestrictNonProductionMailListener::class)->handle($event));
        $this->assertEquals('[develop] Foo Bar Baz', $event->message->getSubject());
    }

    #[Test]
    public function itBlocksTheEmailWhenTheToIsNotWhitelisted(): void
    {
        $this->app['env'] = 'develop';
        $this->useHerdMailer();

        $event = $this->makeEvent('someone@example.com');

        $this->assertFalse(app(RestrictNonProductionMailListener::class)->handle($event));
    }
}
