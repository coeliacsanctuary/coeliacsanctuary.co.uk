<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\CreateEateryReviewAction;
use App\Jobs\EatingOut\ProcessReviewImagesJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\RequestFactories\EateryCreateReviewRequestFactory;
use Tests\TestCase;

class CreateEateryReviewActionTest extends TestCase
{
    use WithFaker;

    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itCreatesAFullRatingThatIsNotApproved(): void
    {
        $this->assertEmpty($this->eatery->reviews);

        $data = [
            ...EateryCreateReviewRequestFactory::new()
                ->state([
                    'rating' => 4,
                    'name' => 'Foo Bar',
                    'email' => 'foo@bar.com',
                ])
                ->create(),
            'ip' => $this->faker->ipv4,
            'approved' => false,
        ];

        $this->callAction(CreateEateryReviewAction::class, $this->eatery, $data);

        $this->assertNotEmpty($this->eatery->reviews()->withoutGlobalScopes()->get());

        $review = EateryReview::query()->withoutGlobalScopes()->first();

        $this->assertFalse($review->approved);
        $this->assertEquals(4, $review->rating);
        $this->assertEquals('Foo Bar', $review->name);
        $this->assertEquals('foo@bar.com', $review->email);
    }

    #[Test]
    public function itDispatchesTheProcessReviewImagesJobIfThereAreImagesInThePayload(): void
    {
        $data = [
            ...EateryCreateReviewRequestFactory::new()
                ->state([
                    'rating' => 4,
                    'name' => 'Foo Bar',
                    'email' => 'foo@bar.com',
                    'images' => [$this->faker->uuid],
                ])
                ->create(),
            'ip' => $this->faker->ipv4,
            'approved' => false,
        ];

        $this->callAction(CreateEateryReviewAction::class, $this->eatery, $data);

        Queue::assertPushed(ProcessReviewImagesJob::class);
    }

    #[Test]
    public function itDoesntDispatchTheJobIfThereIsNoImages(): void
    {
        $data = [
            ...EateryCreateReviewRequestFactory::new()
                ->state([
                    'rating' => 4,
                    'name' => 'Foo Bar',
                    'email' => 'foo@bar.com',
                ])
                ->create(),
            'ip' => $this->faker->ipv4,
            'approved' => false,
        ];

        $this->callAction(CreateEateryReviewAction::class, $this->eatery, $data);

        Queue::assertNotPushed(ProcessReviewImagesJob::class);
    }
}
