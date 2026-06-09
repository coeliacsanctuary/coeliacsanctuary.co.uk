<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Collection;

use App\Services\EatingOut\Collection\Builder\ValueObjects\Average;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Count;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Join;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Order;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use App\Services\EatingOut\Collection\Configuration;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ConfigurationTest extends TestCase
{
    // --- Constructor deduplication ---

    #[Test]
    public function itDeduplicatesDuplicateWhereObjectsInConstructor(): void
    {
        $where = new Where('foo', '=', 'bar');

        $config = new Configuration([$where, $where]);

        $this->assertCount(1, $config->getWheres());
    }

    #[Test]
    public function itDeduplicatesDuplicateWhereArraysInConstructor(): void
    {
        $where = ['foo', '=', 'bar'];

        $config = new Configuration([$where, $where]);

        $this->assertCount(1, $config->getWheres());
    }

    #[Test]
    public function itDeduplicatesDuplicateJoinObjectsInConstructor(): void
    {
        $join = new Join('foo', 'foo.bar', '=', 'wheretoeat.baz');

        $config = new Configuration(joins: [$join, $join]);

        $this->assertCount(1, $config->getJoins());
    }

    #[Test]
    public function itDeduplicatesDuplicateJoinArraysInConstructor(): void
    {
        $join = ['foo', 'foo.bar', '=', 'wheretoeat.baz'];

        $config = new Configuration(joins: [$join, $join]);

        $this->assertCount(1, $config->getJoins());
    }

    #[Test]
    public function itDeduplicatesDuplicateCountObjectsInConstructor(): void
    {
        $count = new Count('reviews', 'reviews.wheretoeat_id', 'wheretoeat.id', 'reviews_count', '>=', 5);

        $config = new Configuration(counts: [$count, $count]);

        $this->assertCount(1, $config->getCounts());
    }

    #[Test]
    public function itDeduplicatesDuplicateAverageObjectsInConstructor(): void
    {
        $average = new Average('reviews', 'rating', 'reviews.wheretoeat_id', 'wheretoeat.id', 'average_rating', '>=', 4.5);

        $config = new Configuration(averages: [$average, $average]);

        $this->assertCount(1, $config->getAverages());
    }

    #[Test]
    public function itDeduplicatesDuplicateOrderObjectsInConstructor(): void
    {
        $order = new Order('foo', 'desc');

        $config = new Configuration(orderBy: [$order, $order]);

        $this->assertCount(1, $config->getOrderings());
    }

    #[Test]
    public function itDeduplicatesDuplicateNestedWhereGroupsInConstructor(): void
    {
        $group = [new Where('foo', '=', 'bar'), new Where('baz', '=', 'bop', 'or')];

        $config = new Configuration([$group, $group]);

        $this->assertCount(1, $config->getWheres());
    }

    // --- addWhere deduplication ---

    #[Test]
    public function itDeduplicatesWhenAddingTheSameWhereTwiceViaAddWhere(): void
    {
        $where = new Where('foo', '=', 'bar');

        $config = new Configuration();
        $config->addWhere($where);
        $config->addWhere($where);

        $this->assertCount(1, $config->getWheres());
    }

    #[Test]
    public function itDeduplicatesWhenAddingTheSameWhereArrayTwiceViaAddWhere(): void
    {
        $config = new Configuration();
        $config->addWhere([new Where('foo', '=', 'bar'), new Where('baz', '=', 'bop', 'or')]);
        $config->addWhere([new Where('foo', '=', 'bar'), new Where('baz', '=', 'bop', 'or')]);

        $this->assertCount(1, $config->getWheres());
    }

    // --- addJoin deduplication ---

    #[Test]
    public function itDeduplicatesWhenAddingTheSameJoinTwiceViaAddJoin(): void
    {
        $join = new Join('foo', 'foo.bar', '=', 'wheretoeat.baz');

        $config = new Configuration();
        $config->addJoin($join);
        $config->addJoin($join);

        $this->assertCount(1, $config->getJoins());
    }

    // --- Distinct items are preserved ---

    #[Test]
    public function itKeepsDistinctWheresIntact(): void
    {
        $config = new Configuration([
            new Where('foo', '=', 'bar'),
            new Where('baz', '=', 'bop'),
        ]);

        $this->assertCount(2, $config->getWheres());
    }

    #[Test]
    public function itKeepsDistinctJoinsIntact(): void
    {
        $config = new Configuration(joins: [
            new Join('foo', 'foo.bar', '=', 'wheretoeat.baz'),
            new Join('qux', 'qux.id', '=', 'wheretoeat.qux_id'),
        ]);

        $this->assertCount(2, $config->getJoins());
    }

    #[Test]
    public function itKeepsDistinctNestedWhereGroupsIntact(): void
    {
        $config = new Configuration([
            [new Where('foo', '=', 'bar'), new Where('baz', '=', 'bop', 'or')],
            [new Where('dit', '=', 'dot'), new Where('zap', '=', 'zip', 'or')],
        ]);

        $this->assertCount(2, $config->getWheres());
    }

    #[Test]
    public function itKeepsDistinctOrderingsIntact(): void
    {
        $config = new Configuration(orderBy: [
            new Order('foo', 'desc'),
            new Order('bar', 'asc'),
        ]);

        $this->assertCount(2, $config->getOrderings());
    }
}
