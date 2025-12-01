<?php

declare(strict_types=1);

namespace Tests\Unit\Mailables\EatingOut;

use App\Models\EatingOut\Eatery;
use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\MjmlMessage;
use App\Mailables\EatingOut\EateryRecommendationAlreadyExistsMailable;
use App\Models\EatingOut\EateryRecommendation;
use Tests\TestCase;

class EateryRecommendationAlreadyExistsMailableTest extends TestCase
{
    #[Test]
    public function itReturnsAnMjmlMessageInstance(): void
    {
        $this->assertInstanceOf(
            MjmlMessage::class,
            EateryRecommendationAlreadyExistsMailable::make($this->create(Eatery::class), $this->create(EateryRecommendation::class), 'foo'),
        );
    }

    #[Test]
    public function itHasTheSubjectSet(): void
    {
        /** @var Eatery $eatery */
        $eatery = $this->create(Eatery::class);

        /** @var EateryRecommendation $recommendation */
        $recommendation = $this->create(EateryRecommendation::class);

        $mailable = EateryRecommendationAlreadyExistsMailable::make($eatery, $recommendation, 'foo');

        $this->assertEquals("Your recommendation of {$eatery->name} already exists in the Coeliac Sanctuary eating out guide!", $mailable->subject);
    }

    #[Test]
    public function itHasTheCorrectView(): void
    {
        $mailable = EateryRecommendationAlreadyExistsMailable::make($this->create(Eatery::class), $this->create(EateryRecommendation::class), 'foo');

        $this->assertEquals('mailables.mjml.eating-out.recommended-eatery-already-exists', $mailable->mjml);
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
            'reason' => fn ($reason) => $this->assertEquals('to let you know that the place you suggested already exists in the Coeliac Sanctuary eating out guide.', $reason),
        ];

        $mailable = EateryRecommendationAlreadyExistsMailable::make($eatery, $recomendation, 'foo');
        $emailData = $mailable->data();

        foreach ($data as $key => $closure) {
            $this->assertArrayHasKey($key, $emailData);
            $closure($emailData[$key]);
        }
    }
}
