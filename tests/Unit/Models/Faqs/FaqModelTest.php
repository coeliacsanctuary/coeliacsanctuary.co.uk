<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Faqs;

use App\Models\Blogs\Blog;
use App\Models\Faqs\Faq;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FaqModelTest extends TestCase
{
    #[Test]
    public function itHasAFaqableRelationship(): void
    {
        $faq = $this->build(Faq::class)->on($this->create(Blog::class))->create();

        $this->assertInstanceOf(MorphTo::class, $faq->faqable());
    }

    #[Test]
    public function itReturnsFaqsInPositionOrderRatherThanIdOrder(): void
    {
        /** @var Blog $blog */
        $blog = $this->create(Blog::class);

        $this->build(Faq::class)->on($blog)->create(['question' => 'Third', 'position' => 2]);
        $this->build(Faq::class)->on($blog)->create(['question' => 'First', 'position' => 0]);
        $this->build(Faq::class)->on($blog)->create(['question' => 'Second', 'position' => 1]);

        $this->assertSame(
            ['First', 'Second', 'Third'],
            $blog->faqs()->pluck('question')->all()
        );
    }

    /**
     * Nova's repeater deletes and recreates every FAQ without setting a position, so
     * they all land on the default. Insertion order has to survive that.
     */
    #[Test]
    public function itFallsBackToIdOrderWhenPositionsAreEqual(): void
    {
        /** @var Blog $blog */
        $blog = $this->create(Blog::class);

        $this->build(Faq::class)->on($blog)->create(['question' => 'First', 'position' => 0]);
        $this->build(Faq::class)->on($blog)->create(['question' => 'Second', 'position' => 0]);
        $this->build(Faq::class)->on($blog)->create(['question' => 'Third', 'position' => 0]);

        $this->assertSame(
            ['First', 'Second', 'Third'],
            $blog->faqs()->pluck('question')->all()
        );
    }
}
