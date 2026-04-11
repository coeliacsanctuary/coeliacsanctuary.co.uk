<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Collection\Builder;

use App\Services\EatingOut\Collection\Builder\QueryBuilder;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Average;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Count;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Join;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Order;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use App\Services\EatingOut\Collection\Configuration;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

abstract class QueryBuilderTestCase extends TestCase
{
    abstract protected function getBuilder(Configuration $configuration): QueryBuilder;

    #[Test]
    abstract public function itReturnsTheBaseSelectedColumns(): void;

    #[Test]
    abstract public function itUsesTheCorrectTable(): void;

    #[Test]
    public function itCanFilterOnCountsFromACountObject(): void
    {
        $count = new Count('reviews', 'reviews.wheretoeat_id', 'wheretoeat.id', 'reviews_count', '>=', 5);

        $sql = $this->getBuilder(new Configuration(counts: [$count]))->toSql();

        $this->assertStringContainsString('(select count(*) from reviews where reviews.wheretoeat_id = wheretoeat.id) as reviews_count', $sql);
        $this->assertStringContainsString('having `reviews_count` >= 5', $sql);
    }

    #[Test]
    public function itCanFilterOnCountsFromACountArray(): void
    {
        $count = ['reviews', 'reviews.wheretoeat_id', 'wheretoeat.id', 'reviews_count', '>=', 5];

        $sql = $this->getBuilder(new Configuration(counts: [$count]))->toSql();

        $this->assertStringContainsString('(select count(*) from reviews where reviews.wheretoeat_id = wheretoeat.id) as reviews_count', $sql);
        $this->assertStringContainsString('having `reviews_count` >= 5', $sql);
    }

    #[Test]
    public function itCanFilterOnAveragesFromAnAverageObject(): void
    {
        $average = new Average('reviews', 'rating', 'reviews.wheretoeat_id', 'wheretoeat.id', 'average_rating', '>=', 4.5);

        $sql = $this->getBuilder(new Configuration(averages: [$average]))->toSql();

        $this->assertStringContainsString('(select avg(rating) from reviews where reviews.wheretoeat_id = wheretoeat.id) as average_rating', $sql);
        $this->assertStringContainsString('having `average_rating` >= 4.5', $sql);
    }

    #[Test]
    public function itCanFilterOnAveragesFromAnAverageArray(): void
    {
        $average = ['reviews', 'rating', 'reviews.wheretoeat_id', 'wheretoeat.id', 'average_rating', '>=', 4.5];

        $sql = $this->getBuilder(new Configuration(averages: [$average]))->toSql();

        $this->assertStringContainsString('(select avg(rating) from reviews where reviews.wheretoeat_id = wheretoeat.id) as average_rating', $sql);
        $this->assertStringContainsString('having `average_rating` >= 4.5', $sql);
    }

    #[Test]
    public function itCanHaveAJoinAddedFromAJoinObject(): void
    {
        $join = new Join('foo', 'foo.bar', '=', 'wheretoeat.baz');

        $sql = $this->getBuilder(new Configuration(joins: [$join]))->toSql();

        $this->assertStringContainsString('join `foo` on `foo`.`bar` = `wheretoeat`.`baz`', $sql);
    }

    #[Test]
    public function itCanHaveAJoinAddedFromAJoinArray(): void
    {
        $join = ['foo', 'foo.bar', '=', 'wheretoeat.baz'];

        $sql = $this->getBuilder(new Configuration(joins: [$join]))->toSql();

        $this->assertStringContainsString('join `foo` on `foo`.`bar` = `wheretoeat`.`baz`', $sql);
    }

    #[Test]
    public function itCanHaveASimpleWhereAppliedFromAWhereObject(): void
    {
        $where = new Where('foo', '=', 'bar');
        $sql = $this->getBuilder(new Configuration([$where]))->toSql();

        $this->assertStringContainsString('(`foo` = \'bar\')', $sql);
    }

    #[Test]
    public function itCanHaveASimpleWhereAppliedFromAWhereArray(): void
    {
        $where = ['foo', '=', 'bar'];
        $sql = $this->getBuilder(new Configuration([$where]))->toSql();

        $this->assertStringContainsString('(`foo` = \'bar\')', $sql);
    }

    #[Test]
    public function itCanHaveMultipleWheresAppliedWithAnOrFromAnObject(): void
    {
        $firstWhere = new Where('foo', '=', 'bar');
        $orWhere = new Where('baz', '=', 'bop', 'or');
        $sql = $this->getBuilder(new Configuration([$firstWhere, $orWhere]))->toSql();

        $this->assertStringContainsString('(`foo` = \'bar\' or `baz` = \'bop\')', $sql);
    }

    #[Test]
    public function itCanHaveMultipleWheresAppliedWithAnOrFromAnArray(): void
    {
        $firstWhere = ['foo', '=', 'bar'];
        $orWhere = ['baz', '=', 'bop', 'or'];
        $sql = $this->getBuilder(new Configuration([$firstWhere, $orWhere]))->toSql();

        $this->assertStringContainsString('(`foo` = \'bar\' or `baz` = \'bop\')', $sql);
    }

    #[Test]
    public function itCanHaveMultipleWheresAppliedWithAnAndFromAnObject(): void
    {
        $firstWhere = new Where('foo', '=', 'bar');
        $andWhere = new Where('baz', '=', 'bop', 'and');
        $sql = $this->getBuilder(new Configuration([$firstWhere, $andWhere]))->toSql();

        $this->assertStringContainsString('(`foo` = \'bar\' and `baz` = \'bop\')', $sql);
    }

    #[Test]
    public function itCanHaveMultipleWheresAppliedWithAnAndFromAnArray(): void
    {
        $firstWhere = ['foo', '=', 'bar'];
        $andWhere = ['baz', '=', 'bop', 'and'];
        $sql = $this->getBuilder(new Configuration([$firstWhere, $andWhere]))->toSql();

        $this->assertStringContainsString('(`foo` = \'bar\' and `baz` = \'bop\')', $sql);
    }

    #[Test]
    public function itCanHaveNestedWheresAppliedAsObject(): void
    {
        $baseWhere = new Where('foo', '=', 'bar');
        $firstNestedWhere = new Where('baz', '=', 'bop');
        $secondNestedWhere = new Where('dit', '=', 'dot');
        $sql = $this->getBuilder(new Configuration([$baseWhere, [$firstNestedWhere, $secondNestedWhere]]))->toSql();

        $this->assertStringContainsString('(`foo` = \'bar\' and (`baz` = \'bop\' and `dit` = \'dot\'))', $sql);
    }

    #[Test]
    public function itCanHaveNestedWheresAppliedAsArray(): void
    {
        $baseWhere = ['foo', '=', 'bar'];
        $firstNestedWhere = ['baz', '=', 'bop'];
        $secondNestedWhere = ['dit', '=', 'dot'];
        $sql = $this->getBuilder(new Configuration([$baseWhere, [$firstNestedWhere, $secondNestedWhere]]))->toSql();

        $this->assertStringContainsString('(`foo` = \'bar\' and (`baz` = \'bop\' and `dit` = \'dot\'))', $sql);
    }

    #[Test]
    public function itCanHaveAnOrderClauseAddedAsObject(): void
    {
        $order = new Order('foo', 'desc');

        $sql = $this->getBuilder(new Configuration(orderBy: [$order]))->toSql();

        $this->assertStringContainsString('order by `foo` desc', $sql);
    }

    #[Test]
    public function itCanHaveAnOrderClauseAddedAsArray(): void
    {
        $order = ['foo', 'desc'];

        $sql = $this->getBuilder(new Configuration(orderBy: [$order]))->toSql();

        $this->assertStringContainsString('order by `foo` desc', $sql);
    }

    #[Test]
    public function itCanHaveMultipleOrderClausesAddedAsObject(): void
    {
        $firstOrder = new Order('foo', 'desc');
        $secondOrder = new Order('bar', 'asc');

        $sql = $this->getBuilder(new Configuration(orderBy: [$firstOrder, $secondOrder]))->toSql();

        $this->assertStringContainsString('order by `foo` desc, `bar` asc', $sql);
    }

    #[Test]
    public function itCanHaveMultipleOrderClausesAddedAsArray(): void
    {
        $firstOrder = ['foo', 'desc'];
        $secondOrder = ['bar', 'asc'];

        $sql = $this->getBuilder(new Configuration(orderBy: [$firstOrder, $secondOrder]))->toSql();

        $this->assertStringContainsString('order by `foo` desc, `bar` asc', $sql);
    }

    #[Test]
    public function itCanHaveAnOrderClauseFromARelationAddedAsObject(): void
    {
        $order = new Order('town.name', 'asc', 'towns', 'id', 'town_id');

        $sql = $this->getBuilder(new Configuration(orderBy: [$order]))->toSql();

        $this->assertStringContainsString('join `towns` on `id` = `town_id`', $sql);
        $this->assertStringContainsString('order by `town`.`name` asc', $sql);
    }

    #[Test]
    public function itCanHaveAnOrderClauseFromARelationAddedAsArray(): void
    {
        $order = ['town.name', 'asc', 'towns', 'id', 'town_id'];

        $sql = $this->getBuilder(new Configuration(orderBy: [$order]))->toSql();

        $this->assertStringContainsString('join `towns` on `id` = `town_id`', $sql);
        $this->assertStringContainsString('order by `town`.`name` asc', $sql);
    }

    #[Test]
    public function itCanHaveALimitApplied(): void
    {
        $sql = $this->getBuilder(new Configuration(limit: 100))->toSql();

        $this->assertStringContainsString('limit 100', $sql);
    }
}
