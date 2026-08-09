<?php

declare(strict_types=1);

namespace Tests\Unit\Mailables\EatingOut;

use App\Models\EatingOut\Eatery;
use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\MjmlMessage;
use App\Mailables\EatingOut\EateryRecommendationAddedMailable;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryReview;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Collection;
use Tests\TestCase;

class EateryRecommendationAddedMailableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

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

    #[Test]
    public function theNearbyEateriesExcludeTheRecommendedEateryAndAnyThatHaveClosedDown(): void
    {
        /** @var Eatery $eatery */
        $eatery = $this->create(Eatery::class);

        /** @var Eatery $nearby */
        $nearby = $this->create(Eatery::class);

        $this->build(Eatery::class)->closedDown()->create();

        $nearbyEateries = $this->nearbyEateriesFor($eatery);

        $this->assertCount(1, $nearbyEateries);
        $this->assertTrue($nearby->is($nearbyEateries->first()));
    }

    #[Test]
    public function theNearbyEateriesHaveTheirVenueTypeAndApprovedRatingsLoaded(): void
    {
        /** @var Eatery $eatery */
        $eatery = $this->create(Eatery::class);

        /** @var Eatery $nearby */
        $nearby = $this->create(Eatery::class, ['venue_type_id' => 2]);

        $this->build(EateryReview::class)->count(2)->approved()->on($nearby)->create(['rating' => 4]);
        $this->build(EateryReview::class)->on($nearby)->create(['rating' => 1]);

        /** @var Eatery $nearbyEatery */
        $nearbyEatery = $this->nearbyEateriesFor($eatery)->first();

        $this->assertTrue($nearbyEatery->relationLoaded('venueType'));
        $this->assertEquals(2, $nearbyEatery->venueType?->id);
        $this->assertEquals(2, $nearbyEatery->rating_count);
        $this->assertEquals(4.0, (float) $nearbyEatery->rating);
    }

    #[Test]
    public function anUnratedNearbyEateryHasNoRating(): void
    {
        /** @var Eatery $eatery */
        $eatery = $this->create(Eatery::class);

        $this->create(Eatery::class);

        /** @var Eatery $nearbyEatery */
        $nearbyEatery = $this->nearbyEateriesFor($eatery)->first();

        $this->assertEquals(0, $nearbyEatery->rating_count);
        $this->assertNull($nearbyEatery->rating);
    }

    /** @return Collection<int, Eatery> */
    protected function nearbyEateriesFor(Eatery $eatery): Collection
    {
        /** @var Collection<int, Eatery> $nearbyEateries */
        $nearbyEateries = EateryRecommendationAddedMailable::make($eatery, $this->create(EateryRecommendation::class), 'foo')
            ->data()['nearbyEateries'];

        return $nearbyEateries;
    }
}
