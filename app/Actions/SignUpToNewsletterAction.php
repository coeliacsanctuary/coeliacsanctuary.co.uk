<?php

declare(strict_types=1);

namespace App\Actions;

use Spatie\MailcoachSdk\Mailcoach;

class SignUpToNewsletterAction
{
    public function handle(string $emailAddress): void
    {
        dispatch(
            fn () => app(Mailcoach::class)->createSubscriber(config('mailcoach-sdk.newsletter_id'), [
                'email' => $emailAddress,
                'skip_confirmation' => true,
            ])
        );
    }
}
