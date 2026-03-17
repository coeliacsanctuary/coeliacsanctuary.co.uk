<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Collection\Builder;

use App\Services\EatingOut\Collection\Builder\BranchQueryBuilder;
use App\Services\EatingOut\Collection\Builder\EateryQueryBuilder;
use App\Services\EatingOut\Collection\Builder\QueryBuilder;
use App\Services\EatingOut\Collection\Configuration;
use PHPUnit\Framework\Attributes\Test;

class BranchQueryBuilderTest extends QueryBuilderTestCase
{
    protected function getBuilder(Configuration $configuration): QueryBuilder
    {
        return new BranchQueryBuilder($configuration);
    }

    #[Test]
    public function itReturnsTheBaseSelectedColumns(): void
    {
        $sql = $this->getBuilder(new Configuration())->toSql();

        $this->assertStringContainsString('select `wheretoeat`.`id` as `id`, `wheretoeat_nationwide_branches`.`id` as `branch_id`, `if(wheretoeat_nationwide_branches`.`name = "" or wheretoeat_nationwide_branches`.`name is null, concat(wheretoeat`.`name, "-", wheretoeat`.`id), concat(wheretoeat_nationwide_branches`.`name, " ", wheretoeat`.`name))` as `ordering`', $sql);
    }

    #[Test]
    public function itUsesTheCorrectTable(): void
    {
        $sql = $this->getBuilder(new Configuration())->toSql();

        $this->assertStringContainsString('from `wheretoeat_nationwide_branches`', $sql);
    }

    #[Test]
    public function itHasTheLiveAndClosedDownChecks(): void
    {
        $sql = $this->getBuilder(new Configuration())->toSql();

        $this->assertStringContainsString('inner join `wheretoeat` on `wheretoeat`.`id` = `wheretoeat_nationwide_branches`.`wheretoeat_id`', $sql);
        $this->assertStringContainsString('`wheretoeat`.`live` = 1', $sql);
        $this->assertStringContainsString('`wheretoeat`.`closed_down` = 0', $sql);
        $this->assertStringContainsString('`wheretoeat_nationwide_branches`.`live` = 1', $sql);
    }
}
