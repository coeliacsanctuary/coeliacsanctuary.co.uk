<?php

declare(strict_types=1);

namespace Tests\Unit\Casts;

use App\Actions\Shop\PrepareOrderForEmailAction;
use App\Casts\EmailDataCast;
use App\Models\Blogs\Blog;
use App\Models\Comments\Comment;
use App\Models\Comments\CommentReply;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use App\Models\NotificationEmail;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use Carbon\Carbon;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmailDataCastTest extends TestCase
{
    protected EmailDataCast $cast;

    protected NotificationEmail $notificationEmail;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->cast = new EmailDataCast();
        $this->notificationEmail = new NotificationEmail();
    }

    /** @return array<string, mixed> */
    protected function castGet(array $data): array
    {
        return $this->cast->get($this->notificationEmail, 'data', (string) json_encode($data), []);
    }

    /** @return array<string, mixed> */
    protected function castSet(mixed $value): array
    {
        return (array) json_decode((string) $this->cast->set($this->notificationEmail, 'data', $value, []), true);
    }

    protected function comment(array $attributes = []): Comment
    {
        /** @var Comment $comment */
        $comment = $this->build(Comment::class)
            ->on($this->create(Blog::class))
            ->state($attributes)
            ->create();

        return $comment;
    }

    #[Test]
    public function itCastsTheDateToACarbonInstance(): void
    {
        $data = $this->castGet(['date' => '2026-09-23 10:30:00']);

        $this->assertInstanceOf(Carbon::class, $data['date']);
        $this->assertSame('2026-09-23 10:30:00', $data['date']->toDateTimeString());
    }

    #[Test]
    public function itResolvesACommentById(): void
    {
        $comment = $this->comment();

        $data = $this->castGet(['comment' => ['id' => $comment->id]]);

        $this->assertTrue($comment->is($data['comment']));
    }

    #[Test]
    public function itResolvesACommentByCreatedAtAndNameWhenThereIsNoId(): void
    {
        $this->travelTo('2026-09-23 10:30:00');

        $comment = $this->comment(['name' => 'Jamie']);

        $this->comment(['name' => 'Someone Else']);

        $data = $this->castGet(['comment' => ['created_at' => '2026-09-23 10:30:00', 'name' => 'Jamie']]);

        $this->assertTrue($comment->is($data['comment']));
    }

    #[Test]
    public function itReturnsANullCommentWhenThereIsNothingToLookItUpBy(): void
    {
        $data = $this->castGet(['comment' => ['foo' => 'bar']]);

        $this->assertArrayHasKey('comment', $data);
        $this->assertNull($data['comment']);
    }

    #[Test]
    public function itResolvesACommentReply(): void
    {
        /** @var CommentReply $reply */
        $reply = $this->build(CommentReply::class)->on($this->create(Blog::class))->create();

        $data = $this->castGet(['reply' => ['id' => $reply->id]]);

        $this->assertTrue($reply->is($data['reply']));
    }

    #[Test]
    public function itResolvesAnEateryReviewAsTheRating(): void
    {
        /** @var EateryReview $review */
        $review = $this->build(EateryReview::class)->approved()->create();

        $data = $this->castGet(['rating' => ['id' => $review->id]]);

        $this->assertTrue($review->is($data['rating']));
    }

    #[Test]
    public function itResolvesAShopCustomerAsTheNotifiable(): void
    {
        /** @var ShopCustomer $customer */
        $customer = $this->create(ShopCustomer::class);

        $data = $this->castGet(['notifiable' => ['id' => $customer->id]]);

        $this->assertTrue($customer->is($data['notifiable']));
    }

    #[Test]
    public function itResolvesAnEateryRecommendation(): void
    {
        /** @var EateryRecommendation $recommendation */
        $recommendation = $this->create(EateryRecommendation::class);

        $data = $this->castGet(['recommendation' => ['id' => $recommendation->id]]);

        $this->assertTrue($recommendation->is($data['recommendation']));
    }

    #[Test]
    public function itResolvesAnEatery(): void
    {
        /** @var Eatery $eatery */
        $eatery = $this->create(Eatery::class);

        $data = $this->castGet(['eatery' => ['id' => $eatery->id]]);

        $this->assertTrue($eatery->is($data['eatery']));
    }

    #[Test]
    public function itResolvesANationwideBranch(): void
    {
        /** @var NationwideBranch $branch */
        $branch = $this->build(NationwideBranch::class)->forEatery($this->create(Eatery::class))->create();

        $data = $this->castGet(['branch' => ['id' => $branch->id]]);

        $this->assertTrue($branch->is($data['branch']));
    }

    #[Test]
    public function itResolvesNearbyEateriesToACollectionOfEateries(): void
    {
        /** @var Eatery $first */
        $first = $this->create(Eatery::class);

        /** @var Eatery $second */
        $second = $this->create(Eatery::class);

        $data = $this->castGet(['nearbyEateries' => [['id' => $first->id], ['id' => $second->id]]]);

        $this->assertInstanceOf(Collection::class, $data['nearbyEateries']);
        $this->assertSame([$first->id, $second->id], $data['nearbyEateries']->pluck('id')->all());
    }

    #[Test]
    public function itResolvesAShopOrder(): void
    {
        /** @var ShopOrder $order */
        $order = $this->create(ShopOrder::class);

        $data = $this->castGet(['order' => ['id' => $order->id]]);

        $this->assertTrue($order->is($data['order']));
    }

    #[Test]
    public function itPreparesTheShopOrderForTheEmail(): void
    {
        /** @var ShopOrder $order */
        $order = $this->create(ShopOrder::class);

        $this->expectAction(PrepareOrderForEmailAction::class, [fn (ShopOrder $given) => $given->is($order)], return: $order);

        $data = $this->castGet(['order' => ['id' => $order->id]]);

        $this->assertSame($order, $data['order']);
    }

    #[Test]
    public function itThrowsWhenAReferencedModelNoLongerExists(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->castGet(['eatery' => ['id' => 12345]]);
    }

    #[Test]
    public function itPassesThroughScalarValues(): void
    {
        $data = $this->castGet([
            'reviewLink' => 'https://www.coeliacsanctuary.co.uk/review/abc123',
            'attempt' => 3,
            'isReminder' => true,
        ]);

        $this->assertSame('https://www.coeliacsanctuary.co.uk/review/abc123', $data['reviewLink']);
        $this->assertSame(3, $data['attempt']);
        $this->assertTrue($data['isReminder']);
    }

    #[Test]
    public function itDropsValuesThatArentScalarsOrKnownModelKeys(): void
    {
        $data = $this->castGet(['meta' => ['foo' => 'bar'], 'reason' => null]);

        $this->assertArrayNotHasKey('meta', $data);
        $this->assertArrayNotHasKey('reason', $data);
    }

    #[Test]
    public function itReturnsAnEmptyArrayWhenThereIsNoData(): void
    {
        $this->assertSame([], $this->castGet([]));
    }

    #[Test]
    public function itStoresACarbonInstanceAsADateTimeString(): void
    {
        $data = $this->castSet(['date' => Carbon::parse('2026-09-23 10:30:00')]);

        $this->assertSame('2026-09-23 10:30:00', $data['date']);
    }

    #[Test]
    public function itStoresACommentAsAnIdCreatedAtAndName(): void
    {
        $this->travelTo('2026-09-23 10:30:00');

        $comment = $this->comment(['name' => 'Jamie']);

        $data = $this->castSet(['comment' => $comment]);

        $this->assertSame([
            'id' => $comment->id,
            'created_at' => '2026-09-23 10:30:00',
            'name' => 'Jamie',
        ], $data['comment']);
    }

    #[Test]
    public function itStoresAnyOtherModelAsJustAnId(): void
    {
        /** @var Eatery $eatery */
        $eatery = $this->create(Eatery::class);

        $data = $this->castSet(['eatery' => $eatery]);

        $this->assertSame(['id' => $eatery->id], $data['eatery']);
    }

    #[Test]
    public function itStoresACollectionOfModelsAsAListOfIds(): void
    {
        /** @var Eatery $first */
        $first = $this->create(Eatery::class);

        /** @var Eatery $second */
        $second = $this->create(Eatery::class);

        $data = $this->castSet(['nearbyEateries' => collect([$first, $second])]);

        $this->assertSame([['id' => $first->id], ['id' => $second->id]], $data['nearbyEateries']);
    }

    #[Test]
    public function itDropsNonModelsFromACollectionAndReindexesWhatsLeft(): void
    {
        /** @var Eatery $eatery */
        $eatery = $this->create(Eatery::class);

        $data = $this->castSet(['nearbyEateries' => collect(['foo', $eatery])]);

        $this->assertSame([['id' => $eatery->id]], $data['nearbyEateries']);
    }

    #[Test]
    public function itStoresScalarValuesAndNullsAsTheyAre(): void
    {
        $data = $this->castSet([
            'reviewLink' => 'https://www.coeliacsanctuary.co.uk/review/abc123',
            'attempt' => 3,
            'isReminder' => true,
            'reason' => null,
        ]);

        $this->assertSame('https://www.coeliacsanctuary.co.uk/review/abc123', $data['reviewLink']);
        $this->assertSame(3, $data['attempt']);
        $this->assertTrue($data['isReminder']);
        $this->assertArrayHasKey('reason', $data);
        $this->assertNull($data['reason']);
    }

    #[Test]
    public function itDropsValuesThatArentModelsDatesCollectionsOrScalars(): void
    {
        $data = $this->castSet(['meta' => ['foo' => 'bar'], 'object' => (object) ['foo' => 'bar']]);

        $this->assertSame([], $data);
    }

    #[Test]
    public function itStoresAnEmptyArrayWhenTheValueIsntAnArray(): void
    {
        $this->assertSame('[]', $this->cast->set($this->notificationEmail, 'data', 'not an array', []));
    }

    #[Test]
    public function itRoundTripsADateAndAModelBackOutOfStorage(): void
    {
        /** @var Eatery $eatery */
        $eatery = $this->create(Eatery::class);

        $stored = (string) $this->cast->set($this->notificationEmail, 'data', [
            'date' => Carbon::parse('2026-09-23 10:30:00'),
            'eatery' => $eatery,
            'reviewLink' => 'https://www.coeliacsanctuary.co.uk/review/abc123',
        ], []);

        $data = $this->cast->get($this->notificationEmail, 'data', $stored, []);

        $this->assertSame('2026-09-23 10:30:00', $data['date']->toDateTimeString());
        $this->assertTrue($eatery->is($data['eatery']));
        $this->assertSame('https://www.coeliacsanctuary.co.uk/review/abc123', $data['reviewLink']);
    }
}
