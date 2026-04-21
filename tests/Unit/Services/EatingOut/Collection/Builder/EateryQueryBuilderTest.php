<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Collection\Builder;

use App\Services\EatingOut\Collection\Builder\EateryQueryBuilder;
use App\Services\EatingOut\Collection\Builder\QueryBuilder;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use App\Services\EatingOut\Collection\Configuration;
use PHPUnit\Framework\Attributes\Test;

class EateryQueryBuilderTest extends QueryBuilderTestCase
{
    protected function getBuilder(Configuration $configuration): QueryBuilder
    {
        return new EateryQueryBuilder($configuration);
    }

    #[Test]
    public function itReturnsTheBaseSelectedColumns(): void
    {
        $sql = $this->getBuilder(new Configuration())->toSql();

        $this->assertStringContainsString('select `wheretoeat`.`id`, `wheretoeat`.`name` as `ordering`, null as branch_id', $sql);
    }

    #[Test]
    public function itUsesTheCorrectTable(): void
    {
        $sql = $this->getBuilder(new Configuration())->toSql();

        $this->assertStringContainsString('from `wheretoeat`', $sql);
    }

    #[Test]
    public function itHasTheLiveAndClosedDownChecks(): void
    {
        $sql = $this->getBuilder(new Configuration())->toSql();

        $this->assertStringContainsString('`wheretoeat`.`live` = 1', $sql);
        $this->assertStringContainsString('`wheretoeat`.`closed_down` = 0', $sql);
    }

    #[Test]
    public function itResolvesParentPlaceholderInWhereClausesToTheCorrectTable(): void
    {
        $where = new Where('[parent].town_id', '=', 1);
        $sql = $this->getBuilder(new Configuration([$where]))->toSql();

        $this->assertStringContainsString('`wheretoeat`.`town_id` = 1', $sql);
    }
}
