<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Collection\Builder\ValueObjects;

use App\Services\EatingOut\Collection\Builder\ValueObjects\Join;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class JoinTest extends TestCase
{
    #[Test]
    public function itSerializesAllPropertiesInOrder(): void
    {
        $join = new Join('foo', 'foo.bar', '=', 'wheretoeat.baz');

        $this->assertSame(
            ['foo', 'foo.bar', '=', 'wheretoeat.baz'],
            $join->jsonSerialize(),
        );
    }

    #[Test]
    public function itAppliesTheJoinToTheQueryBuilder(): void
    {
        $join = new Join('foo', 'foo.bar', '=', 'wheretoeat.baz');

        $sql = $join(DB::table('wheretoeat'))->toSql();

        $this->assertStringContainsString('join `foo` on `foo`.`bar` = `wheretoeat`.`baz`', $sql);
    }

    #[Test]
    public function itReturnsTheQueryBuilder(): void
    {
        $join = new Join('foo', 'foo.bar', '=', 'wheretoeat.baz');
        $builder = DB::table('wheretoeat');

        $result = $join($builder);

        $this->assertSame($builder, $result);
    }
}
