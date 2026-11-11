<?php

declare(strict_types=1);

namespace Tests\Unit\Rules\Shop;

use App\Rules\Shop\ReviewProductIsDistinctRule;
use Closure;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReviewProductIsDistinctRuleTest extends TestCase
{
    protected ReviewProductIsDistinctRule $rule;

    /** @var array<int, string> */
    protected array $failures = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->rule = new ReviewProductIsDistinctRule();
        $this->failures = [];
    }

    protected function failureCallback(): Closure
    {
        return function (string $message): void {
            $this->failures[] = $message;
        };
    }

    /** @param array<int, array{id: mixed, type: string}> $products */
    protected function validateRow(array $products, int $index): void
    {
        $this->rule->setData(['products' => $products]);

        $this->rule->validate("products.{$index}.id", $products[$index]['id'], $this->failureCallback());
    }

    #[Test]
    public function itPassesWhenTheProductHasntAlreadyBeenReviewed(): void
    {
        $this->validateRow([
            ['id' => 1, 'type' => 'physical'],
            ['id' => 2, 'type' => 'physical'],
        ], 1);

        $this->assertEmpty($this->failures);
    }

    #[Test]
    public function itFailsWhenTheSameProductIsReviewedTwice(): void
    {
        $this->validateRow([
            ['id' => 1, 'type' => 'physical'],
            ['id' => 1, 'type' => 'physical'],
        ], 1);

        $this->assertEquals(["You've already left a review for this product"], $this->failures);
    }

    #[Test]
    public function itFailsWhenTheSameDigitalCardIsReviewedTwice(): void
    {
        $this->validateRow([
            ['id' => 1, 'type' => 'digital'],
            ['id' => 1, 'type' => 'digital'],
        ], 1);

        $this->assertEquals(["You've already left a review for this product"], $this->failures);
    }

    #[Test]
    public function itPassesWhenAProductAndADigitalCardShareAnId(): void
    {
        $this->validateRow([
            ['id' => 1, 'type' => 'physical'],
            ['id' => 1, 'type' => 'digital'],
        ], 1);

        $this->assertEmpty($this->failures);
    }

    #[Test]
    public function itDoesntFailTheFirstOfTheDuplicatedRows(): void
    {
        $this->validateRow([
            ['id' => 1, 'type' => 'physical'],
            ['id' => 1, 'type' => 'physical'],
        ], 0);

        $this->assertEmpty($this->failures);
    }

    #[Test]
    public function itTreatsAnIdPostedAsAStringAsTheSameProduct(): void
    {
        $this->validateRow([
            ['id' => 1, 'type' => 'physical'],
            ['id' => '1', 'type' => 'physical'],
        ], 1);

        $this->assertEquals(["You've already left a review for this product"], $this->failures);
    }
}
