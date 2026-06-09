<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Collection\Builder\ValueObjects;

use App\Services\EatingOut\Collection\Builder\ValueObjects\Order;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderTest extends TestCase
{
    #[Test]
    public function itSerializesRequiredPropertiesInOrder(): void
    {
        $order = new Order('name', 'asc');

        $this->assertSame(
            ['name', 'asc', null, null, null],
            $order->jsonSerialize(),
        );
    }

    #[Test]
    public function itSerializesAllPropertiesIncludingRelationKeys(): void
    {
        $order = new Order('town.name', 'asc', 'towns', 'id', 'town_id');

        $this->assertSame(
            ['town.name', 'asc', 'towns', 'id', 'town_id'],
            $order->jsonSerialize(),
        );
    }

    #[Test]
    public function publicPropertiesAreAccessible(): void
    {
        $order = new Order('town.name', 'desc', 'towns', 'id', 'town_id');

        $this->assertSame('town.name', $order->column);
        $this->assertSame('desc', $order->direction);
        $this->assertSame('towns', $order->table);
        $this->assertSame('id', $order->localKey);
        $this->assertSame('town_id', $order->foreignKey);
    }

    #[Test]
    public function optionalRelationPropertiesDefaultToNull(): void
    {
        $order = new Order('name', 'asc');

        $this->assertNull($order->table);
        $this->assertNull($order->localKey);
        $this->assertNull($order->foreignKey);
    }
}
