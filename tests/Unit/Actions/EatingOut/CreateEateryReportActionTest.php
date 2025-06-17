<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\EatingOut\CreateEateryReportAction;
use App\Models\EatingOut\Eatery;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CreateEateryReportActionTest extends TestCase
{
    use WithFaker;

    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itCreatesAStandardRating(): void
    {
        $this->assertEmpty($this->eatery->reports);

        $this->callAction(CreateEateryReportAction::class, $this->eatery, 'Foo');

        $this->assertNotEmpty($this->eatery->refresh()->reports);

        $report = $this->eatery->reports->first();
        $this->assertEquals('Foo', $report->details);
    }

    #[Test]
    public function itStoresTheBranchId(): void
    {
        $this->assertEmpty($this->eatery->reports);

        $this->callAction(CreateEateryReportAction::class, $this->eatery, 'Foo', 1);

        $this->assertNotEmpty($this->eatery->refresh()->reports);

        $report = $this->eatery->reports->first();
        $this->assertEquals(1, $report->branch_id);
    }

    #[Test]
    public function itStoresTheBranchName(): void
    {
        $this->assertEmpty($this->eatery->reports);

        $this->callAction(CreateEateryReportAction::class, $this->eatery, 'Foo', 1, 'Bar');

        $this->assertNotEmpty($this->eatery->refresh()->reports);

        $report = $this->eatery->reports->first();
        $this->assertEquals('Bar', $report->branch_name);
    }
}
