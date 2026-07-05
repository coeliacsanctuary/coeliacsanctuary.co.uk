<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\Faqs\Faq;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** @mixin TestCase */
trait FaqableTestTrait
{
    /** @var callable(array<string, mixed>): Model */
    protected $faqableFactoryClosure;

    /** @param callable(array<string, mixed> $parameters): Model $factory */
    protected function setUpFaqsTest(callable $factory): void
    {
        $this->faqableFactoryClosure = $factory;
    }

    #[Test]
    public function itHasAFaqsRelationship(): void
    {
        $item = call_user_func($this->faqableFactoryClosure);

        $this->assertInstanceOf(MorphMany::class, $item->faqs());
    }

    #[Test]
    public function faqsCanBeRetrievedThroughTheRelationship(): void
    {
        $item = call_user_func($this->faqableFactoryClosure);

        $this->build(Faq::class)->on($item)->create();

        $this->assertCount(1, $item->faqs()->get());
    }
}
