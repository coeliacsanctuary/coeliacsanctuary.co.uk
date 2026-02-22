<?php

declare(strict_types=1);

namespace App\Mailables\EatingOut;

use App\Infrastructure\MjmlMessage;
use App\Mailables\BaseMailable;
use App\Models\EatingOut\EateryRecommendation;

class EateryRecommendationAddedSmallBusinessMailable extends BaseMailable
{
    public function __construct(protected EateryRecommendation $recommendation, ?string $emailKey = null)
    {
        parent::__construct($emailKey);
    }

    public function toMail(): MjmlMessage
    {
        return MjmlMessage::make()
            ->subject("Your recommendation of {$this->recommendation->place_name} has been added to my small businesses blog")
            ->mjml('mailables.mjml.eating-out.recommended-eatery-added-to-small-businesses', $this->baseData([
                'recommendation' => $this->recommendation,
                'reason' => 'to let you know that the place you suggested has been added to the small business blog.',
                'email' => $this->recommendation->email,
            ]));
    }
}
