<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\MainSite\SealiacOverviews;

use App\Filament\Resources\MainSite\SealiacOverviews\SealiacOverviewResource;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\SealiacOverview;
use App\Models\Shop\ShopProduct;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SealiacOverviewResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itRegistersOnlyTheListPage(): void
    {
        $this->assertSame(['index'], array_keys(SealiacOverviewResource::getPages()));
    }

    #[Test]
    public function itIsNotGloballySearchable(): void
    {
        $this->assertFalse(SealiacOverviewResource::canGloballySearch());
    }

    #[Test]
    public function itEagerLoadsTheModel(): void
    {
        $this->assertSame(
            ['model'],
            array_keys(SealiacOverviewResource::getEloquentQuery()->getEagerLoads())
        );
    }

    #[Test]
    public function itResolvesTheModelForAnEateryThatIsntLive(): void
    {
        $eatery = $this->build(Eatery::class)->notLive()->create();

        $this->build(SealiacOverview::class)->forEatery($eatery)->create();

        $this->assertTrue(
            $eatery->is(SealiacOverviewResource::getEloquentQuery()->first()->model)
        );
    }

    #[Test]
    public function itResolvesTheModelForANationwideBranchThatIsntLive(): void
    {
        $eatery = $this->create(Eatery::class);
        $branch = $this->build(NationwideBranch::class)->forEatery($eatery)->create(['live' => false]);

        $this->build(SealiacOverview::class)->forNationwideBranch($branch)->create();

        $this->assertTrue(
            $branch->is(SealiacOverviewResource::getEloquentQuery()->first()->model)
        );
    }

    #[Test]
    public function itResolvesTheModelForAProductWithNoVariants(): void
    {
        $product = $this->create(ShopProduct::class);

        $this->build(SealiacOverview::class)->forProduct($product)->create();

        $this->assertTrue(
            $product->is(SealiacOverviewResource::getEloquentQuery()->first()->model)
        );
    }

    #[Test]
    public function itEagerLoadsTheEateryANationwideBranchBelongsTo(): void
    {
        $eatery = $this->create(Eatery::class);
        $branch = $this->build(NationwideBranch::class)->forEatery($eatery)->create();

        $this->build(SealiacOverview::class)->forNationwideBranch($branch)->create();

        $this->assertTrue(
            SealiacOverviewResource::getEloquentQuery()->first()->model->relationLoaded('eatery')
        );
    }
}
