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
}
