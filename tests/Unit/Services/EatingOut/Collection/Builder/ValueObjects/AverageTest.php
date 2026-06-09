<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Collection\Builder\ValueObjects;

use App\Services\EatingOut\Collection\Builder\ValueObjects\Average;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AverageTest extends TestCase
{
    #[Test]
    public function itSerializesAllPropertiesInOrder(): void
    {
        $average = new Average('reviews', 'rating', 'reviews.wheretoeat_id', 'wheretoeat.id', 'average_rating', '>=', 4.5);

        $this->assertSame(
            ['reviews', 'rating', 'reviews.wheretoeat_id', 'wheretoeat.id', 'average_rating', '>=', 4.5],
            $average->jsonSerialize(),
        );
    }

    #[Test]
    public function itAcceptsAnIntegerValue(): void
    {
        $average = new Average('reviews', 'rating', 'reviews.wheretoeat_id', 'wheretoeat.id', 'average_rating', '>=', 4);

        $this->assertSame(4, $average->jsonSerialize()[6]);
    }

    #[Test]
    public function itAcceptsAFloatValue(): void
    {
        $average = new Average('reviews', 'rating', 'reviews.wheretoeat_id', 'wheretoeat.id', 'average_rating', '>=', 3.75);

        $this->assertSame(3.75, $average->jsonSerialize()[6]);
    }

    #[Test]
    public function publicPropertiesAreAccessible(): void
    {
        $average = new Average('reviews', 'rating', 'reviews.wheretoeat_id', 'wheretoeat.id', 'average_rating', '>=', 4.5);

        $this->assertSame('reviews', $average->table);
        $this->assertSame('rating', $average->column);
        $this->assertSame('reviews.wheretoeat_id', $average->localKey);
        $this->assertSame('wheretoeat.id', $average->foreignKey);
        $this->assertSame('average_rating', $average->alias);
        $this->assertSame('>=', $average->operator);
        $this->assertSame(4.5, $average->value);
    }
}
