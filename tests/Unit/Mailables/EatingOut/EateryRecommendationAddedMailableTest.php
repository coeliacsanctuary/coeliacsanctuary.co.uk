<?php

declare(strict_types=1);

namespace Tests\Unit\Mailables\EatingOut;

use App\Models\EatingOut\Eatery;
use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\MjmlMessage;
use App\Mailables\EatingOut\EateryRecommendationAddedMailable;
use App\Models\EatingOut\EateryRecommendation;
use Tests\TestCase;

class EateryRecommendationAddedMailableTest extends TestCase
{
    #[Test]
    public function itReturnsAnMjmlMessageInstance(): void
    {
        $this->assertInstanceOf(
            MjmlMessage::class,
            EateryRecommendationAddedMailable::make($this->create(Eatery::class), $this->create(EateryRecommendation::class), 'foo'),
        );
    }

    #[Test]
    public function itHasTheSubjectSet(): void
    {
        /** @var Eatery $eatery */
        $eatery = $this->create(Eatery::class);

        /** @var EateryRecommendation $recommendation */
        $recommendation = $this->create(EateryRecommendation::class);

        $mailable = EateryRecommendationAddedMailable::make($eatery, $recommendation, 'foo');

        $this->assertEquals("We’ve added {$eatery->name} to the Coeliac Sanctuary eating out guide!", $mailable->subject);
    }

    #[Test]
    public function itHasTheCorrectView(): void
    {
        $mailable = EateryRecommendationAddedMailable::make($this->create(Eatery::class), $this->create(EateryRecommendation::class), 'foo');

        $this->assertEquals('mailables.mjml.eating-out.recommended-eatery-added', $mailable->mjml);
    }

    #[Test]
    public function itHasTheCorrectData(): void
    {
        /** @var Eatery $eatery */
        $eatery = $this->create(Eatery::class);

        /** @var EateryRecommendation $recomendation */
        $recomendation = $this->create(EateryRecommendation::class);

        $data = [
            'eatery' => fn ($assertionEatery) => $this->assertTrue($eatery->is($assertionEatery)),
            'recommendation' => fn ($assertionRecommendation) => $this->assertTrue($recomendation->is($assertionRecommendation)),
            'email' => fn ($email) => $this->assertEquals($recomendation->email, $email),
            'reason' => fn ($reason) => $this->assertEquals('to let you know that we\'ve added your place recommendation to the Coeliac Sanctuary eating out guide.', $reason),
        ];

        $mailable = EateryRecommendationAddedMailable::make($eatery, $recomendation, 'foo');
        $emailData = $mailable->data();

        foreach ($data as $key => $closure) {
            $this->assertArrayHasKey($key, $emailData);
            $closure($emailData[$key]);
        }
    }
}
