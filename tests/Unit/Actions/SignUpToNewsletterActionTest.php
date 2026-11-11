<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\SignUpToNewsletterAction;
use PHPUnit\Framework\Attributes\Test;
use Spatie\MailcoachSdk\Mailcoach;
use Tests\TestCase;

class SignUpToNewsletterActionTest extends TestCase
{
    #[Test]
    public function itUsesMailcoachToCreateASubscriber(): void
    {
        $this->mock(Mailcoach::class)
            ->shouldReceive('createSubscriber')
            ->withArgs(function (string $listId, array $parameters): true {
                $this->assertEquals(config('mailcoach-sdk.newsletter_id'), $listId);
                $this->assertArrayHasKeys(['email', 'skip_confirmation'], $parameters);
                $this->assertEquals('foo@bar.com', $parameters['email']);
                $this->assertTrue($parameters['skip_confirmation']);

                return true;
            });

        $this->callAction(SignUpToNewsletterAction::class, 'foo@bar.com');
    }
}
