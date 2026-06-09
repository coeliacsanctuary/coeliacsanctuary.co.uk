<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Collection\Builder\ValueObjects;

use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WhereTest extends TestCase
{
    #[Test]
    public function itSerializesAllPropertiesInOrder(): void
    {
        $where = new Where('foo', '=', 'bar', 'and');

        $this->assertSame(
            ['foo', '=', 'bar', 'and'],
            $where->jsonSerialize(),
        );
    }

    #[Test]
    public function itDefaultsTheBooleanToAnd(): void
    {
        $where = new Where('foo', '=', 'bar');

        $this->assertSame(['foo', '=', 'bar', 'and'], $where->jsonSerialize());
    }

    #[Test]
    public function itAppliesTheWhereClauseToTheQueryBuilder(): void
    {
        $where = new Where('foo', '=', 'bar');

        $sql = $where(DB::table('wheretoeat'))->toSql();

        $this->assertStringContainsString('`foo` = ?', $sql);
    }

    #[Test]
    public function itAppliesAnOrWhereClauseToTheQueryBuilder(): void
    {
        $builder = DB::table('wheretoeat');
        (new Where('foo', '=', 'bar'))($builder);
        (new Where('baz', '=', 'bop', 'or'))($builder);

        $sql = $builder->toSql();

        $this->assertStringContainsString('`foo` = ?', $sql);
        $this->assertStringContainsString('or `baz` = ?', $sql);
    }

    #[Test]
    public function itReturnsTheQueryBuilder(): void
    {
        $where = new Where('foo', '=', 'bar');
        $builder = DB::table('wheretoeat');

        $result = $where($builder);

        $this->assertSame($builder, $result);
    }

    #[Test]
    public function itReplacesTheParentPlaceholderWithTheGivenTable(): void
    {
        $where = new Where('[parent].foo', '=', 'bar');

        $sql = $where(DB::table('wheretoeat'), 'mytable')->toSql();

        $this->assertStringContainsString('`mytable`.`foo` = ?', $sql);
    }
}
