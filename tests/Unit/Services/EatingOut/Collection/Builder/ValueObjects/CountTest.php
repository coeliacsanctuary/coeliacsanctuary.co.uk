<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Collection\Builder\ValueObjects;

use App\Services\EatingOut\Collection\Builder\ValueObjects\Count;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CountTest extends TestCase
{
    #[Test]
    public function itSerializesAllPropertiesInOrder(): void
    {
        $count = new Count('reviews', 'reviews.wheretoeat_id', 'wheretoeat.id', 'reviews_count', '>=', 5);

        $this->assertSame(
            ['reviews', 'reviews.wheretoeat_id', 'wheretoeat.id', 'reviews_count', '>=', 5],
            $count->jsonSerialize(),
        );
    }

    #[Test]
    public function publicPropertiesAreAccessible(): void
    {
        $count = new Count('reviews', 'reviews.wheretoeat_id', 'wheretoeat.id', 'reviews_count', '>=', 5);

        $this->assertSame('reviews', $count->table);
        $this->assertSame('reviews.wheretoeat_id', $count->localKey);
        $this->assertSame('wheretoeat.id', $count->foreignKey);
        $this->assertSame('reviews_count', $count->alias);
        $this->assertSame('>=', $count->operator);
        $this->assertSame(5, $count->value);
    }
}
