<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Support;

use App\Filament\Support\FindEateryOrBranch;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FindEateryOrBranchTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class, ['name' => 'Gluten Free Kitchen']);
    }

    #[Test]
    public function itReturnsNothingForAnEmptySearch(): void
    {
        $this->assertSame([], app(FindEateryOrBranch::class)->handle(''));
    }

    #[Test]
    public function itFindsAnEateryByName(): void
    {
        $results = app(FindEateryOrBranch::class)->handle('kitchen');

        $this->assertSame(["{$this->eatery->id}:"], array_keys($results));
        $this->assertSame($this->eatery->load(['area', 'town', 'county', 'country'])->full_name, $results["{$this->eatery->id}:"]);
    }

    #[Test]
    public function itFindsABranchByName(): void
    {
        $branch = $this->build(NationwideBranch::class)->forEatery($this->eatery)->create(['name' => 'Station Branch']);

        $results = app(FindEateryOrBranch::class)->handle('station');

        $this->assertSame(["{$this->eatery->id}:{$branch->id}"], array_keys($results));
    }

    #[Test]
    public function itFindsAnUnnamedBranchByItsTown(): void
    {
        $branch = $this->build(NationwideBranch::class)->forEatery($this->eatery)->create(['name' => '', 'town_id' => 1]);

        $results = app(FindEateryOrBranch::class)->handle('crewe');

        $this->assertSame(["{$this->eatery->id}:{$branch->id}"], array_keys($results));
    }

    #[Test]
    public function itDoesNotFindEateriesOrBranchesThatAreNotLive(): void
    {
        $this->create(Eatery::class, ['name' => 'Hidden Bakery', 'live' => false]);
        $this->build(NationwideBranch::class)->forEatery($this->eatery)->create(['name' => 'Hidden Branch', 'live' => false]);

        $this->assertSame([], app(FindEateryOrBranch::class)->handle('hidden'));
    }

    #[Test]
    public function itDoesNotFindLiveBranchesOfAnEateryThatIsNotLive(): void
    {
        $eatery = $this->create(Eatery::class, ['name' => 'Closed Chain', 'live' => false]);

        $this->build(NationwideBranch::class)->forEatery($eatery)->create(['name' => 'Hidden Branch']);

        $this->assertSame([], app(FindEateryOrBranch::class)->handle('hidden'));
    }

    #[Test]
    public function itResolvesTheLabelForAnEatery(): void
    {
        $this->assertSame(
            $this->eatery->load(['area', 'town', 'county', 'country'])->full_name,
            app(FindEateryOrBranch::class)->label("{$this->eatery->id}:"),
        );
    }

    #[Test]
    public function itResolvesTheLabelForABranch(): void
    {
        $branch = $this->build(NationwideBranch::class)->forEatery($this->eatery)->create(['name' => 'Station Branch']);

        $this->assertSame(
            $branch->load(['eatery', 'area', 'town', 'county', 'country'])->full_name,
            app(FindEateryOrBranch::class)->label("{$this->eatery->id}:{$branch->id}"),
        );
    }
}
