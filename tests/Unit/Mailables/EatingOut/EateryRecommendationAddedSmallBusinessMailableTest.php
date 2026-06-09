<?php

declare(strict_types=1);

namespace Tests\Unit\Mailables\EatingOut;

use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\MjmlMessage;
use App\Mailables\EatingOut\EateryRecommendationAddedSmallBusinessMailable;
use App\Models\EatingOut\EateryRecommendation;
use Tests\TestCase;

class EateryRecommendationAddedSmallBusinessMailableTest extends TestCase
{
    #[Test]
    public function itReturnsAnMjmlMessageInstance(): void
    {
        $this->assertInstanceOf(
            MjmlMessage::class,
            EateryRecommendationAddedSmallBusinessMailable::make($this->create(EateryRecommendation::class), 'foo'),
        );
    }

    #[Test]
    public function itHasTheSubjectSet(): void
    {
        /** @var EateryRecommendation $recommendation */
        $recommendation = $this->create(EateryRecommendation::class);

        $mailable = EateryRecommendationAddedSmallBusinessMailable::make($recommendation, 'foo');

        $this->assertEquals("Your recommendation of {$recommendation->place_name} has been added to my small businesses blog", $mailable->subject);
    }

    #[Test]
    public function itHasTheCorrectView(): void
    {
        $mailable = EateryRecommendationAddedSmallBusinessMailable::make($this->create(EateryRecommendation::class), 'foo');

        $this->assertEquals('mailables.mjml.eating-out.recommended-eatery-added-to-small-businesses', $mailable->mjml);
    }

    #[Test]
    public function itHasTheCorrectData(): void
    {
        /** @var EateryRecommendation $recomendation */
        $recomendation = $this->create(EateryRecommendation::class);

        $data = [
            'recommendation' => fn ($assertionRecommendation) => $this->assertTrue($recomendation->is($assertionRecommendation)),
            'email' => fn ($email) => $this->assertEquals($recomendation->email, $email),
            'reason' => fn ($reason) => $this->assertEquals('to let you know that the place you suggested has been added to the small business blog.', $reason),
        ];

        $mailable = EateryRecommendationAddedSmallBusinessMailable::make($recomendation, 'foo');
        $emailData = $mailable->data();

        foreach ($data as $key => $closure) {
            $this->assertArrayHasKey($key, $emailData);
            $closure($emailData[$key]);
        }
    }
}
