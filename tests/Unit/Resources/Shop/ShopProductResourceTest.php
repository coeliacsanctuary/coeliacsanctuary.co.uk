<?php

declare(strict_types=1);

namespace Tests\Unit\Resources\Shop;

use App\Models\Faqs\Faq;
use App\Models\Shop\ShopOrderReview;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use App\Resources\Shop\ShopProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShopProductResourceTest extends TestCase
{
    protected ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withCategoriesAndProducts(1, 1);

        $this->product = ShopProduct::query()->firstOrFail();
    }

    #[Test]
    public function itReturnsNullFaqsWhenThereAreNone(): void
    {
        $this->assertNull($this->resource()['faqs']);
    }

    #[Test]
    public function itReturnsTheFaqsFromTheRelation(): void
    {
        $this->build(Faq::class)->on($this->product)->create(['question' => 'Is this gluten free?', 'answer' => 'Yes!']);

        $faqs = $this->resource()['faqs'];

        $this->assertCount(1, $faqs);
        $this->assertSame('Is this gluten free?', $faqs->first()['question']);
        $this->assertSame('Yes!', $faqs->first()['answer']);
    }

    #[Test]
    public function itDoesNotReturnARatingWhenTheReviewsRelationIsntLoaded(): void
    {
        $this->buildReviewWithItems(['A genuinely useful review']);

        $resource = (new ShopProductResource($this->product))->toArray(new Request());

        $this->assertInstanceOf(MissingValue::class, $resource['rating']);
    }

    #[Test]
    public function itCountsEachReviewOnceEvenWhenItHasSeveralItems(): void
    {
        $this->buildReviewWithItems(['The real review', 'Same as above', 'Same as above']);

        $this->assertSame(1, $this->resource()['rating']['count']);
    }

    #[Test]
    public function itCountsEachReviewOnceInTheRatingBreakdown(): void
    {
        $this->buildReviewWithItems(['The real review', 'Same as above', 'Same as above']);

        $breakdown = $this->resource()['rating']['breakdown']->firstWhere('rating', 5);

        $this->assertSame(1, $breakdown['count']);
    }

    #[Test]
    public function itAveragesTheDeduplicatedReviewsOnly(): void
    {
        $this->buildReviewWithItems(['A five star review'], 5);
        $this->buildReviewWithItems(['A one star review', 'Same as above', 'Same as above'], 1);

        $this->assertSame(3.0, $this->resource()['rating']['average']);
    }

    /** @return array<string, mixed> */
    protected function resource(): array
    {
        $this->product->unsetRelation('reviews')->unsetRelation('faqs');

        $this->product->load(['reviews', 'faqs']);

        return (new ShopProductResource($this->product))->toArray(new Request());
    }

    /** @param array<int, string|null> $reviews */
    protected function buildReviewWithItems(array $reviews, int $rating = 5): void
    {
        $review = $this->build(ShopOrderReview::class)->create();

        foreach ($reviews as $body) {
            $this->build(ShopOrderReviewItem::class)
                ->forReview($review)
                ->forProduct($this->product)
                ->create(['review' => $body, 'rating' => $rating]);
        }
    }
}
